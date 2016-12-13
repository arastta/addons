$(document).ready(function() {
    // Live Price
    $("div[id^='input-option'], select[id^='input-option'], #input-quantity").change(function() {
        $.ajax({
            url: 'index.php?route=product/product/livePrice',
            type: 'post',
            data: $('#input-quantity, #product input[type=\'text\'], #product input[type=\'hidden\'], #product input[type=\'radio\']:checked, #product input[type=\'checkbox\']:checked, #product select, #product textarea'),
            dataType: 'json',
            success: function(json) {
                if (json['model']) {
                    $('.list-unstyled .product-model').html(json['model']);
                }

                if (json['stock']) {
                    $('.list-unstyled .product-stock').html(json['stock']);
                }

                if (json['price']) {
                    $('.list-unstyled .product-price').html(json['price']);
                }

                if (json['special']) {
                    $('.list-unstyled .product-price').html(json['price']);
                    $('.list-unstyled .product-special').html(json['special']);
                }

                if (json['tax']) {
                    $('.list-unstyled .product-tax').html(json['tax']);
                }

                if (json['points']) {
                    $('.list-unstyled .product-points').html(json['points']);
                }
            }
        });
    });
});