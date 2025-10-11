<?php
/**
 * Helper pour générer des balises img responsive avec srcset et lazy loading
 */

function get_responsive_image_html($image_path, $alt = '', $lazy = true, $class = '', $width = null, $height = null) {
    $path_info = pathinfo($image_path);
    $dir = $path_info['dirname'];
    $filename = $path_info['filename'];
    $ext = $path_info['extension'];
    
    // Chemins des versions optimisées
    $mobile_path = "$dir/{$filename}-768w.$ext";
    $optimized_path = "$dir/{$filename}-optimized.$ext";
    
    // Utiliser les versions optimisées si disponibles
    $src = file_exists(__DIR__ . '/' . $optimized_path) ? $optimized_path : $image_path;
    
    $srcset = [];
    if (file_exists(__DIR__ . '/' . $mobile_path)) {
        $srcset[] = "$mobile_path 768w";
    }
    if (file_exists(__DIR__ . '/' . $optimized_path)) {
        $srcset[] = "$optimized_path 1200w";
    } elseif (file_exists(__DIR__ . '/' . $image_path)) {
        $srcset[] = "$image_path 1920w";
    }
    
    $html = '<img src="' . htmlspecialchars($src) . '"';
    
    if (!empty($srcset)) {
        $html .= ' srcset="' . implode(', ', $srcset) . '"';
        $html .= ' sizes="(max-width: 768px) 100vw, (max-width: 1200px) 80vw, 1200px"';
    }
    
    $html .= ' alt="' . htmlspecialchars($alt) . '"';
    
    if ($lazy) {
        $html .= ' loading="lazy" decoding="async"';
    } else {
        $html .= ' fetchpriority="high"';
    }
    
    if ($class) {
        $html .= ' class="' . htmlspecialchars($class) . '"';
    }
    
    if ($width) {
        $html .= ' width="' . (int)$width . '"';
    }
    
    if ($height) {
        $html .= ' height="' . (int)$height . '"';
    }
    
    $html .= '>';
    
    return $html;
}

function get_background_image_srcset($image_path) {
    $path_info = pathinfo($image_path);
    $dir = $path_info['dirname'];
    $filename = $path_info['filename'];
    $ext = $path_info['extension'];
    
    $mobile_path = "$dir/{$filename}-768w.$ext";
    $optimized_path = "$dir/{$filename}-optimized.$ext";
    
    return [
        'mobile' => file_exists(__DIR__ . '/' . $mobile_path) ? $mobile_path : $image_path,
        'desktop' => file_exists(__DIR__ . '/' . $optimized_path) ? $optimized_path : $image_path,
        'original' => $image_path
    ];
}

