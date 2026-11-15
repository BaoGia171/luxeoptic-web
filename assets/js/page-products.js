// Catalog render sản phẩm từ productCatalog

document.addEventListener('DOMContentLoaded', function () {

    const productGrid = document.querySelector('.product-grid');

    if (!productGrid) return;


    function formatPrice(price) {
        return price.toLocaleString('vi-VN') + 'đ';
    }


    productGrid.innerHTML = "";


    productCatalog.forEach(product => {

        const oldPriceHTML = product.oldPrice
            ? `<span class="product-old-price">
                ${formatPrice(product.oldPrice)}
              </span>`
            : "";


        productGrid.innerHTML += `

        <div class="product-card">

            <div class="product-thumb">

                <div class="product-badge-group">
                    <span class="badge badge-dark">
                        LuxeOptic
                    </span>
                </div>


                <button class="product-wishlist-btn">
                    <span class="material-symbols-outlined text-sm">
                        favorite
                    </span>
                </button>


                <a href="product-detail.html?id=${product.id}">
                    <img src="${product.image}" 
                         alt="${product.name}">
                </a>

            </div>


            <div class="product-info">


                <span class="product-series">
                    ${product.series}
                </span>


                <h3 class="product-title">

                    <a href="product-detail.html?id=${product.id}">
                        ${product.name}
                    </a>

                </h3>



                <div class="product-price-row">

                    <div>

                        <span class="product-price">
                            ${formatPrice(product.price)}
                        </span>

                        ${oldPriceHTML}

                    </div>


                    <a href="product-detail.html?id=${product.id}"
                       class="btn btn-primary btn-sm">

                       Chi tiết

                    </a>


                </div>


            </div>


        </div>

        `;

    });


});