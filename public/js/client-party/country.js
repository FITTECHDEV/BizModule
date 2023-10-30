$(function () {
    /*Ajax setup*/
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /**disable city province and state */
    $('#city').show();
    $('#province').show();
    $('#state').show();

    $("#country").keyup(function () {
        var search = $(this).val();
        if (search.length >= 2) {
            $.ajax({
                url: '/get-countries',
                type: 'post',
                data: { query: search },
                dataType: 'json',
                success: function (response) {
                    var len = response.length;
                    $("#countryResult").empty();
                    for (var i = 0; i < len; i++) {
                        var id = response[i]['CountryID'];
                        var name = response[i]['CountryName'];
                        var code = response[i]['CountCode'];
                        $("#countryResult").append("<li value='" + id + "' id='" + code + "'>" + name + "</li>");
                    }
                    $("#countryResult li").bind("click", function () {
                        setText(this);
                    });

                }
            });
        } else if (search.length < 2) {
            $("#countryResult").empty();
        }
    });

    function setText(element) {
        var elementText = $(element).text();
        var elementID = $(element).attr('value');
        var code = $(element).attr('id');
        $("#country").val(elementText);
        $("#countryID").val(elementID);
        $("#countryCode").val(code);

        $("#countryResult").empty();
        /**Search cities and append*/
        searchCity(code);
        /**Search states and append */
        /*searchProvince(code);
        *Search province and append */
    }


    function searchCity(CountryCode) {
        $.ajax({
            url: '/get-cities',
            type: 'get',
            data: { query: CountryCode },
            dataType: 'json',
            success: function (response) {
                var len = response.length;
                $("#city").empty();
                if (len < 1) {
                    /*searchProvince(CountryCode);*/
                } else {
                    for (var i = 0; i < len; i++) {
                        var id = response[i]['CountryID'];
                        var name = response[i]['CountryName'];
                        var code = response[i]['CountCode'];
                        $("#city").editableSelect("add", 'value='+id+' id='+code+''+name);
                    }
                }

            }
        });
    }



    function searchProvince(CountryCode) {
        $.ajax({
            url: '/get-provinces',
            type: 'post',
            data: { query: CountryCode },
            dataType: 'json',
            success: function (response) {
                var len = response.length;
                $("#province").empty();
                if (len < 1) {
                    $('#province').show();
                } else {
                    $('#province').show();
                    for (var i = 0; i < len; i++) {
                        var id = response[i]['CountryID'];
                        var name = response[i]['CountryName'];
                        $("#province").append("<option value='" + id + "'>" + name + "</option>");
                    }
                }

            }
        });
    }

});
