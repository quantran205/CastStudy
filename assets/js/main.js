document.addEventListener('DOMContentLoaded', () => {
    const cycleImages = document.querySelectorAll('.room-cycle-img');

    cycleImages.forEach(img => {
        let interval;
        let currentIndex = 0;
        
        // lấy danh sách ảnh từ data attribute
        const images = JSON.parse(img.getAttribute('data-images'));
        const originalSrc = img.getAttribute('data-original');
        const basePath = 'uploads/rooms/';

        // nếu phòng có trên 1 ảnh mới chạy hiệu ứng
        if (images.length > 1) {
            const container = img.closest('.room-img-container');

            container.addEventListener('mouseenter', () => {
               
                interval = setInterval(() => {
                    currentIndex = (currentIndex + 1) % images.length;
                    img.src = basePath + images[currentIndex];
                }, 800);
            });

            container.addEventListener('mouseleave', () => {
                // chuột ra cái là trả về ảnh gốc ngay và luôn
                clearInterval(interval);
                img.src = originalSrc;
                currentIndex = 0;
            });
        }
    });
});