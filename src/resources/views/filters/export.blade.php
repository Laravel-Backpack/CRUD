<li class="{{ $filter->name }}-class btn btn-default"
    style="background-color: seagreen; color: white;   height: 32px;line-height: 17px;margin: 0px 10px; "
    name="{{ $filter->name }}"
    id="export-button">{{trans('backpack::crud.exel_export')}}
</li>
<li>
    <a href="#"
       class="btn btn-default"
       style="display: none; background-color: lightgrey; color: black;  margin: 0px 10px; height: 32px; line-height: 17px;"
       id="export-button-download">{{trans('backpack::crud.download')}}</a>
</li>

@push('crud_list_scripts')
    <script>
        jQuery(document).ready(function ($) {

            $(".{{ $filter->name }}-class").click(function (e) {
                e.preventDefault();
                var parameter = $(this).attr('name');
                var value = $(this).val();
                var ajax_table = $("#crudTable").DataTable();
                var current_url = ajax_table.ajax.url();
                var new_url = addOrUpdateUriParameter(current_url, parameter, value);

                new_url = normalizeAmpersand(new_url.toString());
                ajax_table.ajax.url(new_url).load();
                make_server_side_ajax_export(new_url + (new_url.indexOf("?") > 0 ? "&" : "?") + "request_type=excel");
            });

            function make_server_side_ajax_export(url) {
                $("#export-button-download").hide();

                var button = $("#export-button");
                var text = button.text();

                button.html("");
                button.html("{{trans('backpack::crud.loadingRecords')}}");

                $.ajax({
                    type: "POST",
                    url: url,
                    data: {},
                    success: function (data) {
                        button.html("");
                        button.html(text);

                        if (data.error.length > 0) {
                            alert(data.error);
                        } else {
                            $("#export-button-download").attr("href", data.download);
                            $("#export-button-download").fadeIn();
                        }
                    }
                });
            }

            $("li[filter-name={{ $filter->name }}]").on('filter:clear', function (e) {
                $(".{{ $filter->name }}-class").val("");
            });
        });
    </script>
@endpush
