document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. TĂNG/GIẢM SỐ LƯỢNG SẢN PHẨM (Trang Chi Tiết) ---
    const btnMinus = document.getElementById('btnMinus');
    const btnPlus = document.getElementById('btnPlus');
    const inputQty = document.getElementById('inputQty');

    if (btnMinus && btnPlus && inputQty) {
        btnMinus.addEventListener('click', function() {
            let currentVal = parseInt(inputQty.value) || 1;
            if (currentVal > 1) {
                inputQty.value = currentVal - 1;
            }
        });

        btnPlus.addEventListener('click', function() {
            let currentVal = parseInt(inputQty.value) || 1;
            inputQty.value = currentVal + 1;
        });

        inputQty.addEventListener('change', function() {
            if (parseInt(this.value) < 1 || isNaN(parseInt(this.value))) {
                this.value = 1;
            }
        });
    }

    // --- 2. TĂNG/GIẢM SỐ LƯỢNG TRONG BẢNG GIỎ HÀNG (Trang Cart) ---
    const cartMinusBtns = document.querySelectorAll('.btn-cart-minus');
    const cartPlusBtns = document.querySelectorAll('.btn-cart-plus');

    cartMinusBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.cart-input-qty');
            if (input) {
                let currentVal = parseInt(input.value) || 1;
                if (currentVal > 1) {
                    input.value = currentVal - 1;
                }
            }
        });
    });

    cartPlusBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.cart-input-qty');
            if (input) {
                let currentVal = parseInt(input.value) || 1;
                input.value = currentVal + 1;
            }
        });
    });

    // --- 3. VALIDATE FORM CHECKOUT (Bắt buộc SĐT & Địa chỉ) ---
    const checkoutForm = id = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const phoneInput = document.getElementById('so_dien_thoai');
            const addressInput = document.getElementById('dia_chi');
            const nameInput = document.getElementById('ho_ten');

            let isValid = true;
            let errorMsg = '';

            // Validate Họ tên
            if (!nameInput || nameInput.value.trim() === '') {
                errorMsg += '- Vui lòng nhập họ và tên.\n';
                isValid = false;
            }

            // Validate Số điện thoại (Chỉ nhận 10-11 chữ số)
            const phoneRegex = /(84|0[3|5|7|8|9])+([0-9]{8})\b/;
            if (!phoneInput || phoneInput.value.trim() === '') {
                errorMsg += '- Vui lòng nhập số điện thoại.\n';
                isValid = false;
            } else if (!phoneRegex.test(phoneInput.value.trim())) {
                errorMsg += '- Số điện thoại không hợp lệ (ví dụ: 0987654321).\n';
                isValid = false;
            }

            // Validate Địa chỉ
            if (!addressInput || addressInput.value.trim() === '') {
                errorMsg += '- Vui lòng nhập địa chỉ nhận hàng.\n';
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
                alert('VUI LÒNG KIỂM TRA LẠI THÔNG TIN:\n\n' + errorMsg);
            }
        });
    }

    // --- 4. CHUYỂN ĐỔI ẢNH GALLERY ---
    const mainImg = document.getElementById('mainImage');
    const thumbImgs = document.querySelectorAll('.thumb-img');

    if (mainImg && thumbImgs.length > 0) {
        thumbImgs.forEach(thumb => {
            thumb.addEventListener('click', function() {
                mainImg.src = this.src;
                thumbImgs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }
});
