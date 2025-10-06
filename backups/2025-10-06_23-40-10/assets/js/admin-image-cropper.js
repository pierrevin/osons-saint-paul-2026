/**
 * AdminImageCropper - Gestion du recadrage, export Blob et upload
 */
(function() {
    class AdminImageCropper {
        constructor() {
            this.cropper = null;
            this.activeInput = null;
            this.activePreset = null;
            this.imageEl = null;
            this.modalId = 'cropImageModal';
            this.bindGlobal();
        }

        bindGlobal() {
            document.addEventListener('change', (e) => {
                const input = e.target;
                if (input.matches('input[type="file"][data-crop]')) {
                    const file = input.files && input.files[0];
                    if (!file) return;
                    const preset = input.getAttribute('data-crop');
                    this.openWithFile(file, preset, input);
                }
            });

            // Intercepter en capture pour être certain de prioriser sur AdminActions
            document.addEventListener('click', (e) => {
                if (e.target && e.target.matches('#' + this.modalId + ' [data-action="confirm-crop"]')) {
                    e.stopPropagation();
                    e.preventDefault();
                    this.confirmCrop();
                    return;
                }
                if (e.target && e.target.matches('#' + this.modalId + ' [data-action="cancel-crop"]')) {
                    e.stopPropagation();
                    e.preventDefault();
                    this.close();
                    return;
                }
            }, true);
        }

        getAspectRatioForPreset(preset) {
            switch (preset) {
                case 'hero': return 16 / 9; // panoramique par défaut
                case 'citation': return 4 / 3;
                case 'member': return 3 / 4;
                default: return NaN; // libre
            }
        }

        openWithFile(file, preset, inputEl) {
            const reader = new FileReader();
            reader.onload = () => {
                this.activeInput = inputEl;
                this.activePreset = preset;
                this.ensureModal();
                const img = document.querySelector('#' + this.modalId + ' .cropper-target');
                img.src = reader.result;
                this.imageEl = img;
                const aspectRatio = this.getAspectRatioForPreset(preset);
                this.initCropper(aspectRatio);
                AdminModal && AdminModal.open && AdminModal.open(this.modalId);
            };
            reader.readAsDataURL(file);
        }

        initCropper(aspectRatio) {
            if (this.cropper) {
                try { this.cropper.destroy(); } catch (e) {}
                this.cropper = null;
            }
            const options = {
                viewMode: 1,
                dragMode: 'move',
                background: false,
                autoCropArea: 1,
                responsive: true,
                checkOrientation: true,
            };
            if (!isNaN(aspectRatio)) {
                options.aspectRatio = aspectRatio;
            }
            this.cropper = new window.Cropper(this.imageEl, options);
        }

        async confirmCrop() {
            if (!this.cropper || !this.activeInput) return;
            // Déterminer dimensions max côté client par preset pour limiter le poids
            const dims = this.getMaxOutputSize(this.activePreset);
            const canvas = this.cropper.getCroppedCanvas({
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
                width: dims.width,
                height: dims.height,
            });
            if (!canvas) {
                this.close();
                return;
            }
            const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.95));
            if (!blob) {
                this.close();
                return;
            }

            // Construire FormData pour upload AJAX
            const form = this.activeInput.closest('form');
            const preset = this.activePreset || 'standard';
            const data = new FormData();
            data.append('action', 'upload_image');
            data.append('preset', preset);
            data.append('image', blob, 'crop.jpg');

            try {
                // Utiliser un chemin absolu pour éviter les problèmes de résolution
                const response = await fetch('/admin/pages/upload_image.php', {
                    method: 'POST',
                    body: data,
                });
                let json;
                try {
                    json = await response.json();
                } catch (parseErr) {
                    const raw = await response.text();
                    throw new Error('Réponse invalide du serveur: ' + raw.substring(0, 200));
                }
                if (!json.success) throw new Error(json.error || 'Erreur upload');

                // Mettre à jour champ caché ou créer si absent
                const targetFieldName = this.activeInput.getAttribute('name');
                const hiddenName = targetFieldName + '_path';
                let hidden = form && form.querySelector('input[type="hidden"][name="' + hiddenName + '"]');
                if (!hidden && form) {
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = hiddenName;
                    form.appendChild(hidden);
                }
                if (hidden) hidden.value = json.path;

                // Aperçu si présent
                const preview = form && form.querySelector('.current-image-preview img');
                if (preview && json.path) {
                    preview.src = '../../' + json.path;
                }

                // Indiquer modification du formulaire
                if (window.adminActions && typeof window.adminActions.markFormChanged === 'function') {
                    window.adminActions.markFormChanged(form);
                }

                // Sauvegarde auto immédiate: ajouter redirect_section et soumettre réellement le formulaire (POST)
                try {
                    if (form) {
                        // Déterminer la section à rediriger (hash > URL > id formulaire)
                        let section = 'dashboard';
                        try {
                            if (window.location.hash) {
                                section = window.location.hash.replace('#','');
                            } else {
                                const params = new URLSearchParams(window.location.search);
                                const qSection = params.get('section');
                                if (qSection) section = qSection;
                            }
                        } catch (e) {}
                        if (section === 'dashboard') {
                            const fid = (form.id || '').toLowerCase();
                            if (fid.includes('citations')) section = 'citations';
                            else if (fid.includes('hero')) section = 'hero';
                            else if (fid.includes('equipe')) section = 'equipe';
                            else if (fid.includes('programme')) section = 'programme';
                        }
                        // Injecter/mettre à jour le champ hidden
                        let redirect = form.querySelector('input[name="redirect_section"]');
                        if (!redirect) {
                            redirect = document.createElement('input');
                            redirect.type = 'hidden';
                            redirect.name = 'redirect_section';
                            form.appendChild(redirect);
                        }
                        redirect.value = section;
                        form.submit();
                    }
                } catch (e) {
                    console.warn('Auto-sauvegarde non effectuée:', e);
                }

                // Nettoyage
                this.close();
                AdminActions && AdminActions.showSuccess && AdminActions.showSuccess('Image téléchargée, optimisée et sauvegardée');
            } catch (e) {
                console.error(e);
                this.close();
                AdminActions && AdminActions.showError && AdminActions.showError(e.message || 'Erreur upload');
            }
        }

        getMaxOutputSize(preset) {
            switch (preset) {
                case 'hero': return { width: 2880, height: 1620 }; // meilleure netteté sur écrans haute densité
                case 'citation': return { width: 1920, height: 1440 };
                case 'member': return { width: 600, height: 800 };
                default: return { width: undefined, height: undefined };
            }
        }

        ensureModal() {
            let modal = document.getElementById(this.modalId);
            if (modal) return;
            modal = document.createElement('div');
            modal.id = this.modalId;
            modal.className = 'modal-overlay';
            modal.style.display = 'none';
            modal.innerHTML = (
                '<div class="modal-container modal-large">' +
                    '<div class="modal-header">' +
                        '<h3>Recadrer l\'image</h3>' +
                        '<button class="modal-close" data-action="cancel-crop">&times;</button>' +
                    '</div>' +
                    '<div class="modal-body">' +
                        '<div style="max-height:420px;overflow:auto">' +
                            '<img class="cropper-target" alt="A recadrer" style="max-width:100%; display:block;" />' +
                        '</div>' +
                    '</div>' +
                    '<div class="modal-actions">' +
                        '<button class="btn btn-secondary" data-action="cancel-crop">Annuler</button>' +
                        '<button class="btn btn-primary" data-action="confirm-crop">Valider</button>' +
                    '</div>' +
                '</div>'
            );
            document.body.appendChild(modal);
        }

        close() {
            if (this.cropper) {
                try { this.cropper.destroy(); } catch (e) {}
                this.cropper = null;
            }
            if (window.adminModal) {
                adminModal.close(this.modalId);
            } else {
                const modal = document.getElementById(this.modalId);
                if (modal) modal.style.display = 'none';
            }
            this.imageEl = null;
            this.activeInput = null;
            this.activePreset = null;
        }
    }

    // Exposition globale
    window.AdminImageCropper = AdminImageCropper;

    document.addEventListener('DOMContentLoaded', () => {
        window.adminImageCropper = new AdminImageCropper();
    });
})();


