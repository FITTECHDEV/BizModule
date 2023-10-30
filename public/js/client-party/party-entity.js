$("#partyEntityType").keyup(function () {
    var search = $(this).val();
    if (search.length >= 2) {
        $.ajax({
            url: '/get-party-entity',
            type: 'post',
            data: { query: search },
            dataType: 'json',
            success: function (response) {
                var len = response.length;
                $("#partyEntityResult").empty();
                for (var i = 0; i < len; i++) {
                    var id = response[i]['CountryID'];
                    var name = response[i]['CountryName'];
                    var code = response[i]['CountCode'];
                    $("#partyEntityResult").append("<li value='" + id + "' id='" + code + "'>" + name + "</li>");
                }
                $("#partyEntityResult li").bind("click", function () {
                    setPartyEntity(this);
                });

            }
        });
    } else if (search.length < 2) {
        $("#partyEntityResult").empty();
    }
});

function setPartyEntity(element) {
    var elementText = $(element).text();
    var elementID = $(element).attr('value');
    var code = $(element).attr('id');
    $("#partyEntityType").val(elementText);
    $("#partyEntityTypeValue").val(elementID);
    $("#partyEntityResult").empty();
}
