        <?php
    // Merged Gallery Logic: Parent + Child images
    $image_urls = array();
    
    // 1. Get images from current listing
    $current_images = get_attached_media('image', $listing_id);
    if (has_post_thumbnail($listing_id)) {
        $image_urls[] = get_the_post_thumbnail_url($listing_id, 'large');
    }
    foreach ($current_images as $img) {
        $url = wp_get_attachment_image_url($img->ID, 'large');
        if (!in_array($url, $image_urls)) {
            $image_urls[] = $url;
        }
    }

    // 2. If it's a child, add parent images
    if ($parent_id > 0) {
        $parent_images = get_attached_media('image', $parent_id);
        if (has_post_thumbnail($parent_id)) {
            $p_thumb = get_the_post_thumbnail_url($parent_id, 'large');
            if (!in_array($p_thumb, $image_urls)) {
                $image_urls[] = $p_thumb;
            }
        }
        foreach ($parent_images as $img) {
            $url = wp_get_attachment_image_url($img->ID, 'large');
            if (!in_array($url, $image_urls)) {
                $image_urls[] = $url;
            }
        }
    }

    // Cap at reasonable total for display (e.g., 20) but we usually show 5 in grid
    $image_count_total = count($image_urls);
?>
        <?php if (!empty($image_urls)): ?>
            <div class="listing-gallery">
                <div class="gallery-grid">
                    <div class="gallery-main" style="background:url('<?php echo esc_url($image_urls[0]); ?>') center/cover; cursor:pointer;" onclick="openLightbox(0)"></div>
                    <div style="display:grid; grid-template-rows:1fr 1fr; gap:10px;">
                        <?php if (isset($image_urls[1])): ?>
                            <div style="background:url('<?php echo esc_url($image_urls[1]); ?>') center/cover; cursor:pointer;" onclick="openLightbox(1)"></div>
                        <?php
        endif; ?>
                        <?php if (isset($image_urls[2])): ?>
                            <div style="background:url('<?php echo esc_url($image_urls[2]); ?>') center/cover; cursor:pointer;" onclick="openLightbox(2)"></div>
                        <?php
        endif; ?>
                    </div>
                    <div style="display:grid; grid-template-rows:1fr 1fr; gap:10px;">
                        <?php if (isset($image_urls[3])): ?>
                            <div style="background:url('<?php echo esc_url($image_urls[3]); ?>') center/cover; cursor:pointer;" onclick="openLightbox(3)"></div>
                        <?php
        endif; ?>
                        <?php if (isset($image_urls[4])): ?>
                            <div style="background:url('<?php echo esc_url($image_urls[4]); ?>') center/cover; cursor:pointer; position:relative;" onclick="openLightbox(4)">
                                <?php if (count($image_urls) > 5): ?>
                                    <div style="position:absolute; inset:0; background:rgba(0,0,0,0.4); color:white; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:1.2em;">+ <?php echo count($image_urls) - 5; ?> more</div>
                                <?php
            endif; ?>
                            </div>
                        <?php
        endif; ?>
                    </div>
                </div>
            </div>

            <!-- Lightbox Modal -->
            <div id="obenlo-lightbox" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.9); z-index:9999; align-items:center; justify-content:center;">
                <button onclick="closeLightbox()" style="position:absolute; top:20px; right:20px; background:none; border:none; color:white; font-size:2em; cursor:pointer;">&times;</button>
                <div style="position:relative; width:80%; height:80%; display:flex; align-items:center; justify-content:center;">
                    <button onclick="prevImage()" style="position:absolute; left:-50px; background:none; border:none; color:white; font-size:3em; cursor:pointer;">&#10094;</button>
                    <img id="lightbox-img" src="" style="max-width:100%; max-height:100%; object-fit:contain;">
                    <button onclick="nextImage()" style="position:absolute; right:-50px; background:none; border:none; color:white; font-size:3em; cursor:pointer;">&#10095;</button>
                </div>
                <div id="lightbox-counter" style="position:absolute; bottom:20px; color:white; font-size:1.1em;"></div>
            </div>

            <script>
                var galleryImages = <?php echo json_encode($image_urls); ?>;
                var currentImageIdx = 0;
                function openLightbox(idx) {
                    if(!galleryImages || galleryImages.length === 0) return;
                    currentImageIdx = idx;
                    document.getElementById('obenlo-lightbox').style.display = 'flex';
                    updateLightbox();
                }
                function closeLightbox() {
                    document.getElementById('obenlo-lightbox').style.display = 'none';
                }
                function prevImage() {
                    currentImageIdx = (currentImageIdx > 0) ? currentImageIdx - 1 : galleryImages.length - 1;
                    updateLightbox();
                }
                function nextImage() {
                    currentImageIdx = (currentImageIdx < galleryImages.length - 1) ? currentImageIdx + 1 : 0;
                    updateLightbox();
                }
                function updateLightbox() {
                    document.getElementById('lightbox-img').src = galleryImages[currentImageIdx];
                    document.getElementById('lightbox-counter').innerText = (currentImageIdx + 1) + ' / ' + galleryImages.length;
                }
            </script>
        <?php
    endif; ?>

