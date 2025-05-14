$(document).ready(function() {
    // Ajax setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Cache DOM elements
    const partyEntityType = $('#partyEntityType');
    const companyIdInput = $('#companyId');
    const firstNameInput = $('#firstName');
    const lastNameInput = $('#lastName');

    // Fetch company details
    function fetchCompanyDetails(entityType, companyId) {
        console.log('fetchCompanyDetails: entityType=', entityType, 'companyId=', companyId);
        $.ajax({
            url: '/company-details', // Adjust to your route (e.g., {{ route('company.details') }})
            method: 'GET',
            data: {
                entityType: entityType,
                companyId: companyId
            },
            success: function(data) {
                console.log('fetchCompanyDetails: Response=', data);
                firstNameInput.val(data.firstName || firstNameInput.val() || '');
                lastNameInput.val(data.lastName || lastNameInput.val() || '');
                $('#addressLine1').val(data.addressLine1 || '');
                $('#addressLine2').val(data.addressLine2 || '');
                $('#countryID').val(data.countryID || '').trigger('change');
                $('#countryCode').val(data.countryCode || '');
                $('#countryName').val(data.countryName || '');
                $('#cityName').val(data.cityName || '');
                $('#zipCode').val(data.zipCode || '');
                $('#provinceName').val(data.provinceName || '');

                if (!data.firstName || !data.lastName) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Contact Details',
                        text: 'Please enter First Name and Last Name manually.'
                    });
                    firstNameInput.prop('readonly', false).focus();
                    lastNameInput.prop('readonly', false);
                }
            },
            error: function(xhr) {
                console.error('Error fetching company details:', xhr.responseJSON?.error || xhr.statusText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to retrieve company details. Please enter details manually.'
                });
                $('.basic-detail-field').prop('readonly', false);
                firstNameInput.focus();
            }
        });
    }

    // Initialize for edit mode
    if (companyIdInput.val() && partyEntityType.val()) {
        console.log('Edit mode: Pre-populating company details', {
            companyId: companyIdInput.val(),
            entityType: partyEntityType.val()
        });
        fetchCompanyDetails(partyEntityType.val(), companyIdInput.val());
    }

    // Party entity autocomplete
    partyEntityType.keyup(function() {
        var search = $(this).val();
        if (search.length >= 2) {
            $.ajax({
                url: '/get-party-entity',
                type: 'post',
                data: { query: search },
                dataType: 'json',
                success: function(response) {
                    $("#partyEntityResult").empty();
                    response.forEach(function(item) {
                        // Adjust fields based on actual response (e.g., EntityID, EntityName)
                        $("#partyEntityResult").append(
                            `<li value="${item.EntityID || item.CountryID}" id="${item.EntityCode || item.CountCode}">${item.EntityName || item.CountryName}</li>`
                        );
                    });
                    $("#partyEntityResult li").bind("click", function() {
                        setPartyEntity(this);
                    });
                },
                error: function(xhr) {
                    console.error('Error fetching party entities:', xhr.responseJSON?.error || xhr.statusText);
                }
            });
        } else {
            $("#partyEntityResult").empty();
        }
    });

    function setPartyEntity(element) {
        var elementText = $(element).text();
        var elementID = $(element).attr('value');
        var code = $(element).attr('id');
        partyEntityType.val(elementText);
        $("#partyEntityTypeValue").val(elementID);
        $("#partyEntityResult").empty();

        // Trigger fetchCompanyDetails if companyId is set
        if (companyIdInput.val()) {
            console.log('Party entity selected, fetching company details', {
                entityType: elementText,
                companyId: companyIdInput.val()
            });
            fetchCompanyDetails(elementText, companyIdInput.val());
        }
    }
});