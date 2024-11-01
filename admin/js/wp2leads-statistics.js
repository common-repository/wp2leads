(function ($) {
    $(document.body).on('click', '#delete_selected_statistics', function () {
        var btn = $(this);
        var sureMessage = btn.data('warningmsg');
        var notselectedMessage = btn.data('notselectedmsg');

        var selected = $('#the-list input:checked');

        if (selected.length < 1) {
            alert(notselectedMessage);
        } else {
            var sure = confirm(sureMessage);

            if (sure) {
                var statistics_ids = [];

                $.each(selected, function (index, statistic) {
                    statistics_ids.push($(statistic).val());
                });

                var data = {
                    action: 'wp2l_delete_selected_statistics',
                    nonce: wp2leads_ajax_object.nonce,
                    statistics_ids: statistics_ids
                };

                $.ajax({
                    type: 'post',
                    url: ajaxurl,
                    data: data,

                    success: function(response) {
                        var decoded;

                        try {
                            decoded = $.parseJSON(response);
                        } catch(err) {
                            decoded = false;
                        }

                        if (decoded) {
                            if (decoded.success) {
                                alert(decoded.message);

                                window.location.reload();
                            } else {
                                alert(decoded.message);
                            }
                        } else {
                            alert('Something went wrong');
                        }
                    }
                });
            }
        }

    });

    $(document.body).on('click', '.delete-statistic-item', function() {
        var id = $(this).data('statistic-id');
        var parentRow = $(this).parents('tr');

        var data = {
            id: id,
            nonce: wp2leads_ajax_object.nonce,
            action: 'wp2l_delete_statistic_item'
        };

        $.ajax({
            type: 'post',
            url: ajaxurl,
            data: data,
            success: function(response) {
                var decoded;

                try {
                    decoded = $.parseJSON(response);
                } catch(err) {
                    decoded = false;
                }

                if (decoded) {
                    if (decoded.success) {
                        alert(decoded.message);
                        parentRow.remove();
                    } else {
                        alert(decoded.message);
                    }
                } else {
                    alert(wp2leads_i18n_get('Something went wrong'));
                }
            }
        });
    });

    $(document.body).on('change', '#active_mapping', function() {
        var selectedMap = $(this).val();

        if ('' !== selectedMap) {
            $('#filter_maps_statistics').submit();
        }
    });
})(jQuery);
