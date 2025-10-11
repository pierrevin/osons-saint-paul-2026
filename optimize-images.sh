#!/bin/bash
# Script pour optimiser les images hero et citation

cd "$(dirname "$0")"

echo "Optimisation des images hero et citations..."

# Pour chaque image hero et citation WebP
for img in uploads/hero_*.webp uploads/citation_*.webp; do
    # Ignorer les versions déjà optimisées
    if [[ "$img" =~ "-768w" ]] || [[ "$img" =~ "-optimized" ]]; then
        continue
    fi
    
    if [ ! -f "$img" ]; then
        continue
    fi
    
    name=$(basename "$img" .webp)
    echo "Processing $name..."
    
    # Version mobile 768px
    if [ ! -f "uploads/${name}-768w.webp" ]; then
        sips -Z 768 "$img" --out "/tmp/${name}-768w.jpg" > /dev/null 2>&1
        cwebp -q 75 "/tmp/${name}-768w.jpg" -o "uploads/${name}-768w.webp" > /dev/null 2>&1
        rm "/tmp/${name}-768w.jpg" 2>/dev/null
        echo "  Created 768w version"
    fi
    
    # Version optimisée de l'original
    if [ ! -f "uploads/${name}-optimized.webp" ]; then
        cwebp -q 75 "$img" -o "uploads/${name}-optimized.webp" > /dev/null 2>&1
        echo "  Created optimized version"
    fi
done

echo "Done!"
echo ""
echo "Tailles des fichiers:"
ls -lh uploads/*-768w.webp uploads/*-optimized.webp 2>/dev/null | awk '{print $5, $9}'

