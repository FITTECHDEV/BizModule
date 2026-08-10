$(document).ready(function () {
    function getUrlParameter(name) {
        let searchParams = new URLSearchParams(window.location.search);
        return searchParams.get(name);
    }

    let requestType = getUrlParameter('type');

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        },
    });

    let stepper1Node = document.querySelector('.bs-stepper');
    stepper1Node.addEventListener('shown.bs-stepper', function (event) {
        if (event.detail.indexStep == 1) {
            let proceed = true;
            let pet = $('#partyEntityType option:selected').val();
            let l_e_t = $('#legalEntityType option:selected').val();
            let country = $('#countryID option:selected').val();
            let companyName = $('#companyName').val();
            let adl1 = $('#addressLine1').val();
            let client = $('#client').val();
            let clientOptionVal = $('#clientOptionVal').val();
            let email = $('#contractEmail').val();
            let clientName = $('#clientCompanyName').val();
            let firstName = $('#firstName').val();
            let lastName = $('#lastName').val();
            let phoneNo = $('#phoneNo').val();

            if (phoneNo.length == 0) {
                $('#phoneNo').addClass('is-invalid');
                window.stepper.to(0);
            } else {
                $('#phoneNo').removeClass('is-invalid');
                proceed = true;
            }

            if (lastName.length == 0) {
                $('#lastName').addClass('is-invalid');
                window.stepper.to(0);
            } else {
                $('#lastName').removeClass('is-invalid');
                proceed = true;
            }

            if (firstName.length == 0) {
                $('#firstName').addClass('is-invalid');
                window.stepper.to(0);
            } else {
                $('#firstName').removeClass('is-invalid');
                proceed = true;
            }

            if (pet.length == 0) {
                $('#partyEntityType').addClass('is-invalid');
                window.stepper.to(0);
            } else {
                $('#partyEntityType').removeClass('is-invalid');
                proceed = true;
            }

            if (requestType === 'accounting') {
                if (l_e_t.length == 0) {
                    $('#legalEntityType').addClass('is-invalid');
                    window.stepper.to(0);
                } else {
                    $('#legalEntityType').removeClass('is-invalid');
                    proceed = true;
                }
            }

            if ($('#companyName').is(':visible')) {
                if (companyName.length == 0) {
                    $('#companyName').addClass('is-invalid');
                    proceed = false;
                } else {
                    $('#companyName').removeClass('is-invalid');
                    proceed = true;
                }
            }

            if ($('#clientCompanyName').is(':visible')) {
                if (clientName.length == 0) {
                    $('#clientCompanyName').addClass('is-invalid');
                    proceed = false;
                } else {
                    $('#clientCompanyName').removeClass('is-invalid');
                    proceed = true;
                }
            }

            if (proceed === false) {
                window.stepper.to(0);
            }

            if (adl1.length == 0) {
                $('#addressLine1').addClass('is-invalid');
                window.stepper.to(0);
            } else {
                $('#addressLine1').removeClass('is-invalid');
                proceed = true;
            }

            if (country.length == 0) {
                $('#countryID').addClass('is-invalid');
                window.stepper.to(0);
            } else {
                $('#countryID').removeClass('is-invalid');
                proceed = true;
            }

            const entities = ['Shipper + Consignee', 'Consignee Only', 'Shipper Only', 'NoSCMIntergration'];
            let criteria = entities.includes($('#partyEntityType').val());

            if ($('#client').attr('required')) {
                if (client.length == 0 && $('#clientOption option:selected').val() == 'fromMaster' && criteria) {
                    $('#client').addClass('is-invalid');
                    window.stepper.to(0);
                } else if (($('#clientID').val() == '' || $('#clientCode').val() == '') && criteria) {
                    $('#client').addClass('is-invalid');
                    window.stepper.to(0);
                    $('#client').after('<span class="text-danger" id="note">* Invalid Input</span>');
                } else {
                    $('#note').remove();
                    $('#client').removeClass('is-invalid');
                    proceed = true;
                }
            } else {
                $('#client').removeClass('is-invalid');
                proceed = true;
            }

            if ($('#clientOptionVal').attr('change')) {
                if (clientOptionVal.length == 0 && $('#clientOption option:selected').val() == '' && criteria) {
                    $('#clientOptionVal').appendClass('is-invalid');
                    window.stepper.to(1);
                } else if (($('#requestID').val() == '' || $('#requestCode').val() == '') && criteria) {
                    $('#clientOptionVal').appendClass('is-invalid');
                    window.stepper.to(1);
                    $('#clientOptionVal').after('<span class="text-danger" id="note">* Invalid Input</span>');
                } else {
                    $('#note').remove();
                    $('#clientOptionVal').removeClass('is-invalid');
                    proceed = true;
                }
            } else {
                $('#clientOptionVal').removeClass('is-invalid');
                proceed = true;
            }

            if (!ValidateEmail(email)) {
                $('#contractEmail').addClass('is-invalid');
                window.stepper.to(0);
            } else {
                $('#contractEmail').removeClass('is-invalid');
            }
        }

        if (event.detail.indexStep == 2) {
            let office_country_select = $('#officeCountry option:selected').val();
            let sales_person_of_interest = $('#salesPIC').val();
            let billingCurrency = $('#billingCurrency option:selected').val();
            let licenceTypeID = $('#licenceTypeID option:selected').val();
            let maxCreditLimit = $('#maxCreditLimit').val();
            let creditDays = $('#creditDays').val();
            let licenceTypeValue = $('#licenceTypeValue').val();
            let attachmentFileNames = $('#attachmentFileNames').val();
            let isOverseas = $('#overseasEntity').is(':checked');

            if (requestType === 'accounting') {
                let dt = $('#debtorType');
                if (typeof dt.attr('required') !== 'undefined' && dt.attr('required') !== false) {
                    if (!dt.val()) {
                        dt.addClass('is-invalid');
                        window.stepper.to(2);
                    } else {
                        dt.removeClass('is-invalid');
                    }
                } else {
                    dt.removeClass('is-invalid');
                }

                let dv = $('#vendorType');
                if (typeof dv.attr('required') !== 'undefined' && dv.attr('required') !== false) {
                    if (!dv.val()) {
                        dv.addClass('is-invalid');
                        window.stepper.to(2);
                    } else {
                        dv.removeClass('is-invalid');
                    }
                } else {
                    dv.removeClass('is-invalid');
                }

                if (!billingCurrency) {
                    $('#billingCurrency').addClass('is-invalid');
                    window.stepper.to(2);
                } else {
                    $('#billingCurrency').removeClass('is-invalid');
                }

                if (!isOverseas) {
                    if (!licenceTypeID) {
                        $('#licenceTypeID').addClass('is-invalid');
                        window.stepper.to(2);
                    } else {
                        $('#licenceTypeID').removeClass('is-invalid');
                    }

                    if (!licenceTypeValue) {
                        $('#licenceTypeValue').addClass('is-invalid');
                        window.stepper.to(2);
                    } else {
                        $('#licenceTypeValue').removeClass('is-invalid');
                    }
                } else {
                    $('#licenceTypeID').removeClass('is-invalid').removeAttr('required');
                    $('#licenceTypeValue').removeClass('is-invalid').removeAttr('required');
                }

                if (!maxCreditLimit) {
                    $('#maxCreditLimit').addClass('is-invalid');
                    window.stepper.to(2);
                } else {
                    $('#maxCreditLimit').removeClass('is-invalid');
                }

                if (!creditDays) {
                    $('#creditDays').addClass('is-invalid');
                    window.stepper.to(2);
                } else {
                    $('#creditDays').removeClass('is-invalid');
                }
            }

            if (requestType === 'operational') {
                $('#billingCurrency').removeAttr('required');
                $('#creditDays').removeAttr('required');
                $('#maxCreditLimit').removeAttr('required');
                $('#licenceTypeID').removeAttr('required');
                $('#licenceTypeValue').removeAttr('required');
                $('#legalEntityType').removeAttr('required');
                $('#attachments').removeAttr('required');
                $('input[name="attachmentNames[]"]').removeAttr('required');
                $('input[name="attachmentDescription[]"]').removeAttr('required');
                $('#client-check').hide();
                $('#vendor-check').hide();
                $('#debtor-check').hide();
                $('#shipper-check').hide();
                $('#consignee-check').hide();

                $('#otherCheck').prop('checked', true);
            }

            if (!office_country_select) {
                $('#officeCountry').addClass('is-invalid');
                window.stepper.to(2);
            } else {
                $('#officeCountry').removeClass('is-invalid');
            }

            let results = validStepTwo();
            if (!results) {
                window.stepper.to(2);
            }
        }
    });

    function validStepThree(status = true) {
        if ($('#clientInput').val() != '' && ($('#clientID').val() == '' || $('#clientCode').val() == '')) {
            $('#clientInput').addClass('is-invalid');
            status = false;
        } else {
            $('#clientInput').removeClass('is-invalid');
        }

        if ($('#shipperInput').val() != '' && ($('#shipperID').val() == '' || $('#shipperCode').val() == '')) {
            $('#shipperInput').addClass('is-invalid');
            status = false;
        } else {
            $('#shipperInput').removeClass('is-invalid');
        }

        if ($('#consigneeInput').val() != '' && ($('#consigneeID').val() == '' || $('#consigneeCode').val() == '')) {
            $('#consigneeInput').addClass('is-invalid');
            status = false;
        } else {
            $('#consigneeInput').removeClass('is-invalid');
        }

        if ($('#otherInput').val() != '' && ($('#otherID').val() == '' || $('#otherCode').val() == '')) {
            $('#otherInput').addClass('is-invalid');
            status = false;
        } else {
            $('#otherInput').removeClass('is-invalid');
        }

        if ($('#debtor').val() != '' && $('#debtorID').val() == '') {
            $('#debtor').addClass('is-invalid');
            status = false;
        } else {
            $('#debtor').removeClass('is-invalid');
        }

        if ($('#vendor').val() != '' && ($('#vendorID').val() == '' || $('#vendorCode').val() == '')) {
            $('#vendor').addClass('is-invalid');
            status = false;
        } else {
            $('#vendor').removeClass('is-invalid');
        }
        return status;
    }

    function validStepTwo(status = true) {
        return status;
    }

    $('#save-step1').on('click', function (e) {
        let save = true;
        let pet = $('#partyEntityType option:selected').val();
        let legal_entity_val = $('#legalEntityType option:selected').val();
        let country = $('#countryID option:selected').val();
        let companyName = $('#companyName').val();
        let clientName = $('#clientCompanyName').val();
        let adl1 = $('#addressLine1').val();
        let client = $('#client').val();
        let clientOptionVal = $('#clientOptionVal').val();
        let email = $('#contractEmail').val();

        if (pet.length == 0) {
            $('#partyEntityType').addClass('is-invalid');
            e.preventDefault();
            save = false;
        } else {
            $('#partyEntityType').removeClass('is-invalid');
        }

        if (legal_entity_val.length == 0) {
            $('#legalEntityType').addClass('is-invalid');
            e.preventDefault();
            save = false;
        } else {
            $('#legalEntityType').removeClass('is-invalid');
        }

        if ($('#companyName').is(':visible')) {
            if (companyName.length == 0) {
                $('#companyName').addClass('is-invalid');
                save = false;
            } else {
                $('#companyName').removeClass('is-invalid');
            }
        }

        if ($('#clientCompanyName').is(':visible')) {
            if (clientName.length == 0) {
                $('#clientCompanyName').addClass('is-invalid');
                save = false;
            } else {
                $('#clientCompanyName').removeClass('is-invalid');
            }
        }

        if (!save) {
            e.preventDefault();
        }

        if (adl1.length == 0) {
            $('#addressLine1').addClass('is-invalid');
            e.preventDefault();
            save = false;
        } else {
            $('#addressLine1').removeClass('is-invalid');
        }

        if (country.length == 0) {
            $('#countryID').addClass('is-invalid');
            e.preventDefault();
            save = false;
        } else {
            $('#countryID').removeClass('is-invalid');
        }

        const entities = ['Shipper + Consignee', 'Consignee Only', 'Shipper Only', 'NoSCMIntergration'];
        let criteria = entities.includes($('#partyEntityType').val());

        if ($('#client').attr('required')) {
            if (client.length == 0 && $('#clientOption option:selected').val() == 'fromMaster' && criteria) {
                $('#client').addClass('is-invalid');
                e.preventDefault();
                save = false;
            } else if (($('#clientID').val() == '' || $('#clientCode').val() == '') && criteria) {
                $('#client').addClass('is-invalid');
                e.preventDefault();
                save = false;
                $('#client').after('<span class="text-danger" id="note">* Invalid Input</span>');
            } else {
                $('#note').remove();
                $('#client').removeClass('is-invalid');
            }
        } else {
            $('#client').removeClass('is-invalid');
        }

        if ($('#clientOptionVal').attr('required')) {
            if (clientOptionVal.length == 0 && $('#clientOption option:selected').val() == 'fromRequest' && criteria) {
                $('#clientOptionVal').addClass('is-invalid');
                e.preventDefault();
                save = false;
            } else if (($('#requestID').val() == '' || $('#requestCode').val() == '') && criteria) {
                $('#clientOptionVal').addClass('is-invalid');
                e.preventDefault();
                save = false;
                $('#clientOptionVal').after('<span class="text-danger" id="note">* Invalid Input</span>');
            } else {
                $('#note').remove();
                $('#clientOptionVal').removeClass('is-invalid');
            }
        } else {
            $('#clientOptionVal').removeClass('is-invalid');
        }

        if (!ValidateEmail(email)) {
            $('#contractEmail').addClass('is-invalid');
            e.preventDefault();
            save = false;
        } else {
            $('#contractEmail').removeClass('is-invalid');
        }

        if (save) {
            $('#clientPartyForm').submit();
        }
    });

    $('#save-step2').on('click', function (e) {
        let save = true;
        let office_country_select = $('#officeCountry option:selected').val();
        let sales_person_of_interest = $('#salesPIC').val();

        if (office_country_select && office_country_select.length == 0) {
            $('#officeCountry').addClass('is-invalid');
            e.preventDefault();
            save = false;
        } else {
            $('#officeCountry').removeClass('is-invalid');
        }

        let results = validStepTwo();
        if (!results) {
            e.preventDefault();
            save = false;
        }

        if (save) {
            $('#clientPartyForm').submit();
        }
    });

    function ValidateEmail(inputEmail) {
        if (inputEmail.length != 0) {
            const emailRegexp = new RegExp(
                /^[a-zA-Z0-9][\-_\.\+\!\#\$\%\&\'\*\/\=\?\^\`\{\|]{0,1}([a-zA-Z0-9][\-_\.\+\!\#\$\%\&\'\*\/\=\?\^\`\{\|]{0,1})*[a-zA-Z0-9]@[a-zA-Z0-9][-\.]{0,1}([a-zA-Z0-9][-\.]{0,1})*[a-zA-Z0-9]\.[a-zA-Z0-9]{1,}([\.\-]{0,1}[a-zA-Z]){0,}[a-zA-Z0-9]{0,}$/i,
            );
            return emailRegexp.test(inputEmail);
        }
        return true;
    }

//     function ValidateEmail(inputEmail) {
//     return true;
// }

    if (!window.isShowView) {
        $('#otherInput').attr('disabled', 'true');
        $('#consigneeInput').attr('disabled', 'true');
        $('#shipperInput').attr('disabled', 'true');
        $('#clientInput').attr('disabled', 'true');
        $('#debtor').attr('disabled', 'true');
        $('#vendor').attr('disabled', 'true');
    }

    $('#otherCheck').on('change', function () {
        let cc = $('#otherCheck').is(':checked') == true ? 1 : 0;
        if (!window.isShowView) {
            if (cc == 0) {
                $('#otherInput').removeAttr('disabled').attr('required', 'true');
                $('#otherID').attr('required', 'true');
                $('#otherCode').attr('required', 'true');
            } else {
                $('#otherInput').attr('disabled', 'true').removeAttr('required');
                $('#otherID').removeAttr('required');
                $('#otherCode').removeAttr('required');
                $('#otherInput').val('');
                $('#otherID').val('');
                $('#otherCode').val('');
            }
        }
    });

    $('#debtorCheck').on('change', function () {
        let isChecked = $(this).is(':checked');
        if (!window.isShowView) {
            if (isChecked) {
                $('#debtor').attr('disabled', true);
            } else {
                $('#debtor').attr('disabled', false);
            }
        }
    });

    $('#vendorCheck').on('change', function () {
        let isChecked = $(this).is(':checked');
        if (!window.isShowView) {
            if (isChecked) {
                $('#vendor').attr('disabled', true);
            } else {
                $('#vendor').attr('disabled', false);
            }
        }
    });

    $('#clientCheck').on('change', function () {
        let cc = $('#clientCheck').is(':checked') == true ? 1 : 0;
        if (!window.isShowView) {
            if (cc == 0) {
                $('#clientInput').removeAttr('disabled');
            } else {
                $('#clientInput').attr('disabled', 'true');
                $('#clientInput').val('');
                $('#clientID').val('');
                $('#clientCode').val('');
            }
        }
    });

    $('#shipperCheck').on('change', function () {
        let cc = $('#shipperCheck').is(':checked') == true ? 1 : 0;
        if (!window.isShowView) {
            if (cc == 0) {
                $('#shipperInput').removeAttr('disabled');
            } else {
                $('#shipperInput').attr('disabled', 'true');
                $('#shipperInput').val('');
                $('#shipperID').val('');
                $('#shipperCode').val('');
            }
        }
    });

    $('#consigneeCheck').on('change', function () {
        let cc = $('#consigneeCheck').is(':checked') == true ? 1 : 0;
        if (!window.isShowView) {
            if (cc == 0) {
                $('#consigneeInput').removeAttr('disabled');
            } else {
                $('#consigneeInput').attr('disabled', 'true');
                $('#consigneeInput').val('');
                $('#consigneeID').val('');
                $('#consigneeCode').val('');
            }
        }
    });

    function PartyEntityType(param) {
        if (!window.isShowView) {
            if (param == 'Client with Debtor or Vendor') {
                if (!$('#otherCheck').attr('disabled')) {
                    $('#otherCheck').attr('disabled', 'true');
                    $('#otherCheck').prop('checked', false);
                } else {
                    $('#otherCheck').prop('checked', false);
                }
                if ($('#clientCheck').attr('disabled')) {
                    $('#clientCheck').removeAttr('disabled');
                    $('#clientCheck').prop('checked', true);
                } else {
                    $('#clientCheck').prop('checked', true);
                }
                if ($('#shipperCheck').attr('disabled')) {
                    $('#shipperCheck').removeAttr('disabled');
                    $('#shipperCheck').prop('checked', true);
                } else {
                    $('#shipperCheck').prop('checked', true);
                }
                if ($('#consigneeCheck').attr('disabled')) {
                    $('#consigneeCheck').removeAttr('disabled');
                    $('#consigneeCheck').prop('checked', true);
                } else {
                    $('#consigneeCheck').prop('checked', true);
                }
            } else if (param == 'Client without Debtor or Vendor') {
                if (!$('#otherCheck').attr('disabled')) {
                    $('#otherCheck').attr('disabled', 'true');
                    $('#otherCheck').prop('checked', false);
                } else {
                    $('#otherCheck').prop('checked', false);
                }
                if ($('#clientCheck').attr('disabled')) {
                    $('#clientCheck').removeAttr('disabled');
                    $('#clientCheck').prop('checked', true);
                } else {
                    $('#clientCheck').prop('checked', true);
                }
                if ($('#shipperCheck').attr('disabled')) {
                    $('#shipperCheck').removeAttr('disabled');
                    $('#shipperCheck').prop('checked', true);
                } else {
                    $('#shipperCheck').prop('checked', true);
                }
                if ($('#consigneeCheck').attr('disabled')) {
                    $('#consigneeCheck').removeAttr('disabled');
                    $('#consigneeCheck').prop('checked', true);
                } else {
                    $('#consigneeCheck').prop('checked', true);
                }
            } else if (param == 'Shipper + Consignee') {
                if (!$('#otherCheck').attr('disabled')) {
                    $('#otherCheck').attr('disabled', 'true');
                    $('#otherCheck').prop('checked', false);
                } else {
                    $('#otherCheck').prop('checked', false);
                }
                if (!$('#clientCheck').attr('disabled')) {
                    $('#clientCheck').attr('disabled', 'true');
                    $('#clientCheck').prop('checked', false);
                } else {
                    $('#clientCheck').prop('checked', false);
                }
                if ($('#shipperCheck').attr('disabled')) {
                    $('#shipperCheck').removeAttr('disabled');
                    $('#shipperCheck').prop('checked', true);
                } else {
                    $('#shipperCheck').prop('checked', true);
                }
                if ($('#consigneeCheck').attr('disabled')) {
                    $('#consigneeCheck').removeAttr('disabled');
                    $('#consigneeCheck').prop('checked', true);
                } else {
                    $('#consigneeCheck').prop('checked', true);
                }
            } else if (param == 'Shipper Only') {
                if (!$('#otherCheck').attr('disabled')) {
                    $('#otherCheck').attr('disabled', 'true');
                    $('#otherCheck').prop('checked', false);
                } else {
                    $('#otherCheck').prop('checked', false);
                }
                if (!$('#clientCheck').attr('disabled')) {
                    $('#clientCheck').attr('disabled', 'true');
                    $('#clientCheck').prop('checked', false);
                } else {
                    $('#clientCheck').prop('checked', false);
                }
                if ($('#shipperCheck').attr('disabled')) {
                    $('#shipperCheck').removeAttr('disabled');
                    $('#shipperCheck').prop('checked', true);
                } else {
                    $('#shipperCheck').prop('checked', true);
                }
                if (!$('#consigneeCheck').attr('disabled')) {
                    $('#consigneeCheck').attr('disabled', 'true');
                    $('#consigneeCheck').prop('checked', false);
                } else {
                    $('#consigneeCheck').prop('checked', false);
                }
            } else if (param == 'Consignee Only') {
                if (!$('#otherCheck').attr('disabled')) {
                    $('#otherCheck').attr('disabled', 'true');
                    $('#otherCheck').prop('checked', false);
                } else {
                    $('#otherCheck').prop('checked', false);
                }
                if (!$('#clientCheck').attr('disabled')) {
                    $('#clientCheck').attr('disabled', 'true');
                    $('#clientCheck').prop('checked', false);
                } else {
                    $('#clientCheck').prop('checked', false);
                }
                if (!$('#shipperCheck').attr('disabled')) {
                    $('#shipperCheck').attr('disabled', 'true');
                    $('#shipperCheck').prop('checked', false);
                } else {
                    $('#shipperCheck').prop('checked', false);
                }
                if ($('#consigneeCheck').attr('disabled')) {
                    $('#consigneeCheck').removeAttr('disabled');
                    $('#consigneeCheck').prop('checked', true);
                } else {
                    $('#consigneeCheck').prop('checked', true);
                }
            } else if (param == 'NoSCMIntergration') {
                if (!$('#otherCheck').attr('disabled')) {
                    $('#otherCheck').attr('disabled', 'true');
                    $('#otherCheck').prop('checked', false);
                } else {
                    $('#otherCheck').prop('checked', false);
                }
                if (!$('#shipperCheck').attr('disabled')) {
                    $('#shipperCheck').attr('disabled', 'true');
                    $('#shipperCheck').removeAttr('checked');
                }
                if (!$('#clientCheck').attr('disabled')) {
                    $('#clientCheck').removeAttr('checked');
                    $('#clientCheck').attr('disabled', 'true');
                }
                if ($('#consigneeCheck').attr('disabled')) {
                    $('#consigneeCheck').removeAttr('checked');
                } else {
                    $('#consigneeCheck').attr('disabled', 'true');
                    $('#consigneeCheck').removeAttr('checked');
                }
            } else {
                if ($('#otherCheck').attr('disabled')) {
                    $('#otherCheck').prop('disabled', false);
                    $('#otherCheck').prop('checked', true);
                } else {
                    $('#otherCheck').prop('checked', true);
                }
                if (!$('#shipperCheck').attr('disabled')) {
                    $('#shipperCheck').attr('disabled', 'true');
                    $('#shipperCheck').removeAttr('checked');
                }
                if (!$('#clientCheck').attr('disabled')) {
                    $('#clientCheck').removeAttr('checked');
                    $('#clientCheck').attr('disabled', 'true');
                }
                if ($('#consigneeCheck').attr('disabled')) {
                    $('#consigneeCheck').removeAttr('checked');
                } else {
                    $('#consigneeCheck').attr('disabled', 'true');
                    $('#consigneeCheck').removeAttr('checked');
                }
            }
        }
    }

    let legalEntityType = $('#legalEntityType');
    let legalEntityTypeValue = legalEntityType.val();

    legalEntityType.on('change', function () {
        legaltype = $(this).val();
        if (!window.isShowView) {
            LegalEntityType(legaltype);
        }
    });

    function LegalEntityType(val) {
        if (!window.isShowView) {
            $('#debtorType').removeClass('is-invalid');
            $('#vendorType').removeClass('is-invalid');

            if (val == 'Debtor') {
                $('#vendorType').prop('selectedIndex', 0).attr('disabled', true).removeAttr('required');
                $('#debtorType').prop('selectedIndex', 0).removeAttr('disabled').prop('required', true);
                $('#debtorCheck').attr('disabled', false);
                $('#vendorCheck').prop('checked', false).attr('disabled', true);
                $('#vendorID').val('');
                $('#vendorCode').val('');
                $('#vendor').val('');
                $('#debtorCheck').prop('checked', true);
                $('#debtorCheckVal').remove();
                $('#vendorCheckVal').remove();
                $('form').append('<input type="hidden" value="on" name="debtorCheck" id="debtorCheckVal" checked>');
            }

            if (val == 'Vendor') {
                $('#debtorType').prop('selectedIndex', 0).attr('disabled', true).removeAttr('required');
                $('#vendorType').prop('selectedIndex', 0).removeAttr('disabled').prop('required', true);
                $('#vendorCheck').attr('disabled', false);
                $('#debtorCheck').prop('checked', false).attr('disabled', true);
                $('#debtorID').val('');
                $('#debtorCode').val('');
                $('#debtor').val('');
                $('#vendorCheck').prop('checked', true);
                $('#debtorCheckVal').remove();
                $('#vendorCheckVal').remove();
                $('form').append('<input type="hidden" value="on" name="vendorCheck" id="vendorCheckVal" checked>');
            }

            if (val == 'Debtor & Vendor') {
                $('#debtorType').prop('selectedIndex', 0).removeAttr('disabled').prop('required', true);
                $('#vendorType').prop('selectedIndex', 0).removeAttr('disabled').prop('required', true);
                $('#vendorCheck').prop('checked', true).attr('disabled', false);
                $('#debtorCheck').prop('checked', true).attr('disabled', false);
                $('#vendorID').val('');
                $('#vendorCode').val('');
                $('#vendor').val('');
                $('#debtorID').val('');
                $('#debtorCode').val('');
                $('#debtor').val('');
                $('#debtorCheckVal').remove();
                $('#vendorCheckVal').remove();
                $('form').append('<input type="hidden" value="on" name="debtorCheck" id="debtorCheckVal" checked>');
                $('form').append('<input type="hidden" value="on" name="vendorCheck" id="vendorCheckVal" checked>');
            }
        }
    }

    if (window.isShowView) {
        $('#admin-details :input').prop('disabled', false).prop('readonly', false);
    }

    if (window.isShowView) {
        console.log('Show view: Admin details enabled', $('#otherInput').prop('disabled'));
    } else {
        console.log('Create view: Initial admin details state', {
            otherInput: $('#otherInput').prop('disabled'),
            debtor: $('#debtor').prop('disabled'),
            vendor: $('#vendor').prop('disabled'),
        });
    }
});
