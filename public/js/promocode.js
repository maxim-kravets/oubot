$(function() {

    $('#activate-promocode').click(function (e) {

        e.preventDefault();

        order_id = $('#orderReference').val();
        promocode_name = $('#promocode').val();

        $.ajax({
            method: 'POST',
            url: '/payment/activate-promocode',
            data: {order_id: order_id, promocode_name: promocode_name},
            success: function (data) {
                if (data['activated']) {

                    console.log(data['new_signature']);

                    $('#price').text(data['new_price'] + 'UAH');
                    $('#amount').attr('value', data['new_price']);
                    $('#productPrice').attr('value', data['new_price']);
                    $('#merchantSignature').attr('value', data['new_signature']);
                    $('#promocode-section').hide();

                    if (data['new_price'] === 0) {
                        $('#buy-btn').hide();
                    }

                    $('#promocode-activation-status').append(
                        "<div class=\"alert alert-success alert-dismissible fade show\" role=\"alert\">" +
                        "  <strong>" + data['reason'] + "</strong>" +
                        "</div>"
                    );
                } else {
                    $('#promocode-activation-status').append(
                        "<br><div class=\"alert alert-danger alert-dismissible fade show\" role=\"alert\">" +
                        "  <strong>" + data['reason'] + "</strong>" +
                        "  <button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\">" +
                        "    <span aria-hidden=\"true\">&times;</span>" +
                        "  </button>" +
                        "</div>"
                    );
                }
            }
        })
    });
});