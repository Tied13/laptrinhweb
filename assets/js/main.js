document.addEventListener('DOMContentLoaded', function () {
    // 1. Slider Banner Trang chủ
    let slides = document.querySelectorAll('.slide');
    if (slides.length > 0) {
        let currentSlide = 0;
        setInterval(() => {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }, 3000);
    }

  
    let thumbs = document.querySelectorAll('.thumb-img');
    let mainImg = document.getElementById('mainImage');
    if (thumbs.length > 0 && mainImg) {
        thumbs.forEach(thumb => {
            thumb.addEventListener('click', function () {
                thumbs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                mainImg.src = this.src;
            });
        });
    }

    // Tăng/Giảm Số Lượng [+] [-]
    let btnMinus = document.getElementById('btnMinus');
    let btnPlus = document.getElementById('btnPlus');
    let inputQty = document.getElementById('inputQty');

    if (btnMinus && btnPlus && inputQty) {
        btnMinus.addEventListener('click', function () {
            let val = parseInt(inputQty.value) || 1;
            if (val > 1) inputQty.value = val - 1;
        });
        btnPlus.addEventListener('click', function () {
            let val = parseInt(inputQty.value) || 1;
            inputQty.value = val + 1;
        });
    }

    // Validate Form Checkout (SĐT + Địa chỉ)
    let checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function (e) {
            let phone = document.getElementById('phone').value.trim();
            let address = document.getElementById('address').value.trim();

            if (phone === '' || address === '') {
                e.preventDefault();
                alert('Vui lòng nhập đầy đủ Số điện thoại và Địa chỉ giao hàng!');
            }
        });
    }
});
document.addEventListener('DOMContentLoaded', function () {
    let slides = document.querySelectorAll('.slide');
    if (slides.length > 0) {
        let currentSlide = 0;
        setInterval(() => {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }, 3500);
    }
});
