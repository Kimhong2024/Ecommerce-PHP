
<!-- Slider Section -->

    <!-- Slider Section -->
<section class="slider my-12 mx-auto max-w-7xl">
    <div class="relative">
        <!-- Slider Track -->
        <div class="slider-track">
            <?php
            require_once 'admin/include/db.php';
            $db = (new Database())->connect();
            $stmt = $db->query("SELECT * FROM sliders WHERE status = 'active' ORDER BY id DESC");
            $sliders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($sliders)) {
                echo '<div class="text-center text-gray-500">No sliders available</div>';
            } else {
                foreach ($sliders as $slider) {
                    ?>
                    <div class="slider-item">
                        <img src="admin/<?php echo htmlspecialchars($slider['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($slider['title']); ?>" 
                             class="slider-image">
                        <div class="slider-content">
                            <h2 class="slider-title"><?php echo htmlspecialchars($slider['title']); ?></h2>
                            <p class="slider-description"><?php echo htmlspecialchars($slider['description1']); ?></p>
                            <p class="slider-description"><?php echo htmlspecialchars($slider['description2']); ?></p>
                            <a href="<?php echo htmlspecialchars($slider['button_link']); ?>" 
                               class="slider-btn bg-blue-500">
                                <?php echo htmlspecialchars($slider['button_text']); ?>
                            </a>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>

        <!-- Navigation Buttons -->
        <button class="slider-button prev">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <button class="slider-button next">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>
</section>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const track = document.querySelector('.slider-track');
    const slides = document.querySelectorAll('.slider-item');
    const prevButton = document.querySelector('.slider-button.prev');
    const nextButton = document.querySelector('.slider-button.next');
    let currentIndex = 0;

    function updateSlider() {
        track.style.transform = `translateX(-${currentIndex * 100}%)`;
    }

    prevButton.addEventListener('click', () => {
        currentIndex = (currentIndex - 1 + slides.length) % slides.length;
        updateSlider();
    });

    nextButton.addEventListener('click', () => {
        currentIndex = (currentIndex + 1) % slides.length;
        updateSlider();
    });

    // Auto slide every 5 seconds
    setInterval(() => {
        currentIndex = (currentIndex + 1) % slides.length;
        updateSlider();
    }, 5000);
});
</script>