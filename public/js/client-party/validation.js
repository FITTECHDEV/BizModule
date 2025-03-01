$(document).ready(function () {
    /*Ajax setup CSRF*/
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let stepper1Node = document.querySelector('.bs-stepper')
    stepper1Node.addEventListener('shown.bs-stepper', function (event) {
        /*Next step one*/
        if (event.detail.indexStep == 1) {
            let proceed = true;
            let pet = $('#partyEntityType option:selected').val();
            // let l_e_t = $('#legalEntityType option:selected').val();
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


            if (pet.length == 0) {
                $('#partyEntityType').addClass('is-invalid');
                window.stepper.to(0);
            } else {
                $('#partyEntityType').removeClass('is-invalid');
                proceed = true;
            }

            // if (l_e_t.length == 0) {
            //     $('#legalEntityType').addClass('is-invalid');
            //     window.stepper.to(0);
            // } else {
            //     $('#legalEntityType').removeClass('is-invalid');
            //     proceed = true;
            // }

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

            /**/
            const entities = ['Shipper + Consignee', 'Consignee Only', 'Shipper Only', 'NoSCMIntergration'];
            let criteria = entities.includes($('#partyEntityType').val());


            if ($('#client').attr('required')) {

                if ((client.length == 0 && $('#clientOption option:selected').val() == 'fromMaster') && criteria) {
                    $('#client').addClass('is-invalid');
                    window.stepper.to(0);
                } else if (($('#clientID').val() == '' || $('#clientCode').val() == '') && criteria) {
                    /*If the data comes form master then clientid and client name should also have values rest invalid*/
                    $('#client').addClass('is-invalid');
                    window.stepper.to(0);
                    $('#client').after('<span class="text-danger" id="note">* Invalid Input</span>')
                } else {
                    $('#note').remove();
                    $('#client').removeClass('is-invalid');
                    proceed = true;
                }
            } else {
                $('#client').removeClass('is-invalid');
                proceed = true;
            }

            if ($('#clientOptionVal').attr('required')) {
                if ((clientOptionVal.length == 0 && $('#clientOption option:selected').val() == 'fromRequest') && criteria) {
                    $('#clientOptionVal').addClass('is-invalid');
                    window.stepper.to(0);
                } else if (($('#requestID').val() == '' || $('#requestCode').val() == '') && criteria) {
                    /*If the data comes form master then clientid and client name should also have values rest invalid*/
                    $('#clientOptionVal').addClass('is-invalid');
                    window.stepper.to(0);
                    $('#clientOptionVal').after('<span class="text-danger" id="note">* Invalid Input</span>')
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
        /*Next Step two*/
        if (event.detail.indexStep == 2) {

            let office_country_select = $('#officeCountry option:selected').val();
            let sales_person_of_interest = $('#salesPIC').val();
            let billingCurrency = $('#billingCurrency option:selected').val();
            let licenceTypeID = $('#licenceTypeID option:selected').val();
            let maxCreditLimit = $('#maxCreditLimit').val();
            let creditDays = $('#creditDays').val();
            let licenceTypeValue = $('#licenceTypeValue').val();
            let attachmentFileNames = $('#attachmentFileNames').val();

            /**legal entity debtorType and vendorType*/
            /**
             * #. check attr required ? validate : no validate
             */
            let dt = $('#debtorType');
            if ((typeof dt.attr('required') !== 'undefined') && dt.attr('required') !== false) {
                /**Validate debtor type val */
                if (dt.val() == null || dt.val().length == 0) {
                    /**Prevent proceed || add isinvalid class */
                    dt.addClass('is-invalid');
                    window.stepper.to(2);

                } else {
                    dt.removeClass('is-invalid');
                }
            } else {
                dt.removeClass('is-invalid');
            }
            let dv = $('#vendorType');
            if ((typeof dv.attr('required') !== 'undefined') && dv.attr('required') !== false) {
                /**Validate debtor type val */
                if (dv.val() == null || dv.val().length == 0) {
                    /**Prevent proceed || add isinvalid class */
                    dv.addClass('is-invalid');
                    window.stepper.to(2);

                } else {
                    dv.removeClass('is-invalid');
                }
            } else {
                dv.removeClass('is-invalid');
            }

            /*billing currency*/
            if (billingCurrency.length == 0) {
                $('#billingCurrency').addClass('is-invalid');
                window.stepper.to(2);
            } else {
                $('#billingCurrency').removeClass('is-invalid');
            }

            /* License Type */
            if (licenceTypeID.length == 0) {
                $('#licenceTypeID').addClass('is-invalid');
                window.stepper.to(2);
            } else {
                $('#licenceTypeID').removeClass('is-invalid');
            }

            // Licence Type Code
            if (licenceTypeValue.length == 0) {
                $('#licenceTypeValue').addClass('is-invalid');
                window.stepper.to(2);
            } else {
                $('#licenceTypeValue').removeClass('is-invalid');
            }
            /*Credit limit*/
            if (maxCreditLimit.length == 0) {
                $('#maxCreditLimit').addClass('is-invalid');
                window.stepper.to(2);
            } else {
                $('#maxCreditLimit').removeClass('is-invalid');
            }

            /* Credit Days */
            if (creditDays.length == 0) {
                $('#creditDays').addClass('is-invalid');
                window.stepper.to(2);
            } else {
                $('#creditDays').removeClass('is-invalid');
            }

            /**Sales PIC */
            if (sales_person_of_interest.length == 0) {
                $('#salesPIC').addClass('is-invalid');
                window.stepper.to(2);
            } else {
                $('#salesPIC').removeClass('is-invalid');
            }
            if ((office_country_select == '') && office_country_select.length == 0) {
                $('#officeCountry').addClass('is-invalid');
                window.stepper.to(2);
            } else {
                $('#officeCountry').removeClass('is-invalid');
            }

            /**Validate step two inputs */
            let results = validStepTwo();
            if (!results) {
                window.stepper.to(2);
            }
        }
    });

    function validStepThree(status = true) {
        /**ADMIN SECTION*/
        /**Client */
        if (($('#clientInput').val() != '') && ($('#clientID').val() == '' || $('#clientCode').val() == '')) {
            $('#clientInput').addClass('is-invalid');
            status = false;
        } else {
            $('#clientInput').removeClass('is-invalid');
        }
        /**Shipper */
        if (($('#shipperInput').val() != '') && ($('#shipperID').val() == '' || $('#shipperCode').val() == '')) {
            $('#shipperInput').addClass('is-invalid');
            status = false;
        } else {
            $('#shipperInput').removeClass('is-invalid');
        }
        /**Consignee */
        if (($('#consigneeInput').val() != '') && ($('#consigneeID').val() == '' || $('#consigneeCode').val() == '')) {
            $('#consigneeInput').addClass('is-invalid');
            status = false;
        } else {
            $('#consigneeInput').removeClass('is-invalid');
        }
        /**Other */
        if (($('#otherInput').val() != '') && ($('#otherID').val() == '' || $('#otherCode').val() == '')) {
            $('#otherInput').addClass('is-invalid');
            status = false;
        } else {
            $('#otherInput').removeClass('is-invalid');
        }
        /**Debtor */
        if (($('#debtor').val() != '') && ($('#debtorID').val() == '')) {
            $('#debtor').addClass('is-invalid');
            status = false;
        } else {
            $('#debtor').removeClass('is-invalid');
        }
        /**Vendor */
        if (($('#vendor').val() != '') && ($('#vendorID').val() == '' || $('#vendorCode').val() == '')) {
            $('#vendor').addClass('is-invalid');
            status = false;
        } else {
            $('#vendor').removeClass('is-invalid');
        }
        return status;
    }

    function validStepTwo(status = true) {
        if (($('#portOfLoadingAir').val() != '') && ($('#portOfLoadingAirID').val() == '')) {
            $('#portOfLoadingAir').addClass('is-invalid');
            status = false;
        } else {
            $('#portOfLoadingAir').removeClass('is-invalid');
        }

        if (($('#portOfLoadingSea').val() != '') && ($('#portOfLoadingSeaID').val() == '')) {
            $('#portOfLoadingSea').addClass('is-invalid');
            status = false;
        } else {
            $('#portOfLoadingSea').removeClass('is-invalid');
        }

        if (($('#portOfDischargeAir').val() != '') && ($('#portOfDischargeAirID').val() == '')) {
            $('#portOfDischargeAir').addClass('is-invalid');
            status = false;
        } else {
            $('#portOfDischargeAir').removeClass('is-invalid');
        }

        if (($('#portOfDischargeSea').val() != '') && ($('#portOfDischargeSeaID').val() == '')) {
            $('#portOfDischargeSea').addClass('is-invalid');
            status = false;
        } else {
            $('#portOfDischargeSea').removeClass('is-invalid');
        }
        /*Sales PIC
        if (($('#salesPIC').val() != '') && ($('#salesPICID').val() == '')){
            $('#salesPIC').addClass('is-invalid');
            status = false;
        }else{
            $('#salesPIC').removeClass('is-invalid');
        }*/
        /*Account PIC*/
        if (($('#accountPIC').val() != '') && ($('#accountPICID').val() == '')) {
            $('#accountPIC').addClass('is-invalid');
            status = false;
        } else {
            $('#accountPIC').removeClass('is-invalid');
        }

        /*Notify Party 1**/
        if (($('#notifyParty1').val() != '') && ($('#notifyParty1ID').val() == '' || $('#notifyParty1Code').val() == '')) {
            $('#notifyParty1').addClass('is-invalid');
            status = false;
        } else {
            $('#notifyParty1').removeClass('is-invalid');
        }

        /**Notify party two */
        if (($('#notifyParty2').val() != '') && ($('#notifyParty2ID').val() == '' || $('#notifyParty2Code').val() == '')) {
            $('#notifyParty2').addClass('is-invalid');
            status = false;
        } else {
            $('#notifyParty1').removeClass('is-invalid');
        }

        if (($('#forwarderName').val() != '') && ($('#forwarderID').val() == '')) {
            $('#forwarderName').addClass('is-invalid');
            status = false;
        } else {
            $('#forwarderName').removeClass('is-invalid');
        }
        return status;
    }

    /** Click save step one */
    $('#save-step1').on('click', function (e) {
        /**validate and submit */
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

        // if (legal_entity_val.length == 0) {
        //     $('#legalEntityType').addClass('is-invalid');
        //     e.preventDefault();
        //     save = false;
        // } else {
        //     $('#legalEntityType').removeClass('is-invalid');
        // }

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
            if ((client.length == 0 && $('#clientOption option:selected').val() == 'fromMaster') && criteria) {
                $('#client').addClass('is-invalid');
                e.preventDefault();
                save = false;
            } else if (($('#clientID').val() == '' || $('#clientCode').val() == '') && criteria) {
                /*If the data comes form master then clientid and client name should also have values rest invalid*/
                $('#client').addClass('is-invalid');
                e.preventDefault();
                save = false;
                $('#client').after('<span class="text-danger" id="note">* Invalid Input</span>')
            } else {
                $('#note').remove();
                $('#client').removeClass('is-invalid');
            }
        } else {
            $('#client').removeClass('is-invalid');
        }

        if ($('#clientOptionVal').attr('required')) {
            if ((clientOptionVal.length == 0 && $('#clientOption option:selected').val() == 'fromRequest') && criteria) {
                $('#clientOptionVal').addClass('is-invalid');
                e.preventDefault();
                save = false;
            } else if (($('#requestID').val() == '' || $('#requestCode').val() == '') && criteria) {
                /*If the data comes form master then clientid and client name should also have values rest invalid*/
                $('#clientOptionVal').addClass('is-invalid');
                e.preventDefault();
                save = false;
                $('#clientOptionVal').after('<span class="text-danger" id="note">* Invalid Input</span>')
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

    /**save step two */
    $('#save-step2').on('click', function (e) {
        /**validate and submit */
        let save = true;
        /* let office_country_disp = $('#officeCountryDisp').val() ;*/
        let office_country_select = $('#officeCountry option:selected').val();
        let sales_person_of_interest = $('#salesPIC').val();

        if (office_country_select && office_country_select.length == 0) {
            $('#officeCountry').addClass('is-invalid');
            e.preventDefault();
            save = false;
        } else {
            $('#officeCountry').removeClass('is-invalid');
        }

        /** if (sales_person_of_interest.length == 0) {
             $('#salesPIC input[name="salesPIC"]').addClass('is-invalid');
             console.log('here');
             e.preventDefault();
             save = false;
         } else {
             $('#salesPIC input[name="salesPIC"]').removeClass('is-invalid');
         } */

        /**Validate step two inputs */
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
                /^[a-zA-Z0-9][\-_\.\+\!\#\$\%\&\'\*\/\=\?\^\`\{\|]{0,1}([a-zA-Z0-9][\-_\.\+\!\#\$\%\&\'\*\/\=\?\^\`\{\|]{0,1})*[a-zA-Z0-9]@[a-zA-Z0-9][-\.]{0,1}([a-zA-Z][-\.]{0,1})*[a-zA-Z0-9]\.[a-zA-Z0-9]{1,}([\.\-]{0,1}[a-zA-Z]){0,}[a-zA-Z0-9]{0,}$/i
            );

            return emailRegexp.test(inputEmail);
        }
        return true;
    }
    /** step three scenario */
    /**
     * LEGAL TYPE
     */
    let legalEntityType = $('#legalEntityType');

    /**Check if the value is set */
    let legalEntityTypeValue = legalEntityType.val();

    /** Make debtor and vendor readonly
    $('#debtorCheck').attr('disabled', true);
    $('#vendorChec').attr('disabled ', true);*/
    $('#debtor').attr('disabled', 'true');
    $('#vendor').attr('disabled', 'true');


    /**For legal entiry show client party purpose*/
    let legaltype = $('#legalEntityType option:selected').val();
    /**Chceck if it is not empty and populate*/
    if (legaltype.length !== 0) {
        LegalEntityType(legaltype);
    }

    /**LegalEntity On change function*/
    legalEntityType.on('change', function () {
        legaltype = $(this).val();
        /**/
        LegalEntityType(legaltype);

    });
    /**
     * Debtor checkbox change
     */


    $('#debtorCheck').on('change', function () {
        let isChecked = $(this).is(':checked');
        if (isChecked) {
            $('#debtor').attr('disabled', true);
        } else {
            $('#debtor').attr('disabled', false);
        }
    });

    /**
    * Vendor checkbox change
    */
    $('#vendorCheck').on('change', function () {
        let isChecked = $(this).is(':checked');
        if (isChecked) {
            $('#vendor').attr('disabled', true);
        } else {
            $('#vendor').attr('disabled', false);
        }
    });
    /**
     * Manages admin details for debtor and vendor
     * @param {string} val
     */
    // function LegalEntityType(val) {
    //     $('#debtorType').removeClass('is-invalid');
    //     $('#vendorType').removeClass('is-invalid');

    //     if (val == "Debtor") {
    //         /* disable vendor legal type*/
    //         $('#vendorType').prop('selectedIndex', 0);
    //         $('#vendorType').attr('disabled', true);
    //         $('#vendorType').removeAttr('required');

    //         /*Enable debtor legal type*/
    //         $('#debtorType').prop('selectedIndex', 0);
    //         $('#debtorType').removeAttr('disabled');
    //         $('#debtorType').prop('required', true);

    //         /**Enable Debtor (admin) */
    //         $('#debtorCheck').attr('disabled', false);

    //         /**Disable vendor  input and checkbox*/
    //         $('#vendorCheck').prop('checked', false);
    //         $('#vendorCheck').attr('disabled', true);
    //         $('#vendorID').val('');
    //         $('#vendorCode').val('');
    //         $('#vendor').val('');


    //         /*Debtor checkbox*/
    //         $('#debtorCheck').prop('checked', true);

    //         $('#debtorCheckVal').remove();
    //         $('#vendorCheckVal').remove();
    //         $('form').append('<input type="hidden" value=on" name="debtorCheck" id="debtorCheckVal" checked>');

    //     }

    //     if (val == "Vendor") {
    //         /* disable debtor legal type*/
    //         $('#debtorType').prop('selectedIndex', 0);
    //         $('#debtorType').attr('disabled', true);
    //         $('#debtorType').removeAttr('required');

    //         /* enable vendor legal type*/
    //         $('#vendorType').prop('selectedIndex', 0);
    //         $('#vendorType').removeAttr('disabled');
    //         $('#vendorType').prop('required', true);


    //         /*Enable Vendor*/
    //         $('#vendorCheck').attr('disabled', false);

    //         /**Disable debtor*/
    //         $('#debtorCheck').prop('checked', false);
    //         $('#debtorCheck').attr('disabled', true);
    //         $('#debtorID').val('');
    //         $('#debtorCode').val('');
    //         $('#debtor').val('');

    //         /*Vendor checkbox*/
    //         $('#vendorCheck').prop('checked', true);

    //         $('#debtorCheckVal').remove();
    //         $('#vendorCheckVal').remove();

    //         $('form').append('<input type="hidden" value="on" name="vendorCheck" id="vendorCheckVal" checked>');

    //     }

    //     if (val == "Debtor & Vendor") {
    //         /** Enable both vendor and debtor legal type */
    //         $('#debtorType').prop('selectedIndex', 0);
    //         $('#debtorType').removeAttr('disabled');
    //         $('#debtorType').prop('required', true);

    //         $('#vendorType').prop('selectedIndex', 0);
    //         $('#vendorType').removeAttr('disabled');
    //         $('#vendorType').prop('required', true);

    //         /*Vendor*/
    //         $('#vendorCheck').prop('checked', true);
    //         $('#vendorCheck').attr('disabled', false);
    //         $('#vendorID').val('');
    //         $('#vendorCode').val('');
    //         $('#vendor').val('');

    //         /*Debtor*/
    //         $('#debtorCheck').prop('checked', true);
    //         $('#debtorCheck').attr('disabled', false);
    //         $('#debtorID').val('');
    //         $('#debtorCode').val('');
    //         $('#debtor').val('');

    //         /*Vendor checkbox*/
    //         $('#vendorCheck').prop('checked', true);

    //         $('#vendorCheckVal').remove();
    //         $('#debtorCheckVal').remove();
    //         /**Append debtor and vendor checkbox to form  */
    //         $('form').append('<input type="hidden" value="on" name="debtorCheck" id="debtorCheckVal" checked>' +
    //             '<input type="hidden" value="on" name="vendorCheck" id="vendorCheckVal" checked>');

    //     }

    //     if ($('#partyEntityType option:selected').val() == 'NoSCMIntergration') {
    //         if (val == "Debtor & Vendor") {
    //             /*Vendor*/
    //             $('#vendorCheck').attr('disabled', true);

    //             /*Debtor*/
    //             $('#debtorCheck').attr('disabled', true);
    //         } else if (val == "Debtor") {
    //             /*Debtor*/
    //             $('#debtorCheck').attr('disabled', true);
    //         } else {
    //             /*Vendor*/
    //             $('#vendorCheck').attr('disabled', true);
    //         }

    //     }
    // }

    /**For show client party purposes*/
    let param = $('#partyEntityType option:selected').val();


    /** Manage client input on prty entity change : basic details section*/
    const entities = ['Shipper + Consignee', 'Consignee Only', 'Shipper Only', 'NoSCMIntergration'];
    criteria = entities.includes(param);

    /*Check if not null and populate*/
    // if (param.length !== 0) {
    //     clientStatus(param);
    //     PartyEntityType(param);
    // }

    /**For on change purposes */
    // $('#partyEntityType').on('change', function () {
    //     param = $('#partyEntityType option:selected').val();
    //     clientStatus(param);
    //     PartyEntityType(param);

    // });

    /**Initialize the inputs*/
    $('#otherInput').attr('disabled', 'true');
    $('#consigneeInput').attr('disabled', 'true');
    $('#shipperInput').attr('disabled', 'true');
    $('#clientInput').attr('disabled', 'true');

    /**
     *  Manages client shipper and consignee admin details
     * @param {string} param
     */
    function PartyEntityType(param) {
        if (param == "Client with Debtor or Vendor") {
            /**for client shipper consignee: disable otherCheck and otherInput*/
            /**Add class disabled to otherCheck uncheck if checked */
            if (!$('#otherCheck').attr('disabled')) {
                $('#otherCheck').attr('disabled', 'true');
                $('#otherCheck').prop('checked', false);
            } else {
                $('#otherCheck').prop('checked', false);
            }
            /**check weather client/shipper/consignee has class disable then remove  And check*/
            /**Cleint */
            if ($('#clientCheck').attr('disabled')) {
                $('#clientCheck').removeAttr('disabled');
                $('#clientCheck').prop('checked', true);
            } else {
                $('#clientCheck').prop('checked', true);
            }
            /**Shipper */
            if ($('#shipperCheck').attr('disabled')) {
                $('#shipperCheck').removeAttr('disabled');
                $('#shipperCheck').prop('checked', true);
            } else {
                $('#shipperCheck').prop('checked', true);
            }
            /**Consignee */
            if ($('#consigneeCheck').attr('disabled')) {
                $('#consigneeCheck').removeAttr('disabled');
                $('#consigneeCheck').prop('checked', true);
            } else {
                $('#consigneeCheck').prop('checked', true);
            }
            /**for shipper + consignee: disable client and other*/
        }else if(param == "Client without Debtor or Vendor"){
            // Other check state then disable
            if (!$('#otherCheck').attr('disabled')) {
                $('#otherCheck').attr('disabled', 'true');
                $('#otherCheck').prop('checked', false);
            } else {
                $('#otherCheck').prop('checked', false);
            }
            // Client check state then enable
            if ($('#clientCheck').attr('disabled')) {
                $('#clientCheck').removeAttr('disabled');
                $('#clientCheck').prop('checked', true);
            } else {
                $('#clientCheck').prop('checked', true);
            }
            // Check Shipper state then enable
            /**Shipper */
            if ($('#shipperCheck').attr('disabled')) {
                $('#shipperCheck').removeAttr('disabled');
                $('#shipperCheck').prop('checked', true);
            } else {
                $('#shipperCheck').prop('checked', true);
            }
            // Check Debtor state then disable
            // Check Vendor State then disable
        }else if (param == "Shipper + Consignee") {
            if (!$('#otherCheck').attr('disabled')) {
                $('#otherCheck').attr('disabled', 'true');
                $('#otherCheck').removeAttr('checked');
            }
            if (!$('#clientCheck').attr('disabled')) {
                $('#clientCheck').attr('disabled', 'true');
                $('#clientCheck').removeAttr('checked');
            }
            /**Enable shipper */
            if ($('#shipperCheck').attr('disabled')) {
                $('#shipperCheck').removeAttr('disabled');
                $('#shipperCheck').prop('checked', true);
            } else {
                $('#shipperCheck').prop('checked', true);
            }
            /**Enable consignee */
            if ($('#consigneeCheck').attr('disabled')) {
                $('#consigneeCheck').removeAttr('disabled');
                $('#consigneeCheck').prop('checked', true);
            } else {
                $('#consigneeCheck').prop('checked', true);
            }
            /**For Shipper only: check and disable client and consignee and other*/
        } else if (param == "Shipper Only") {
            /**Other */
            if (!$('#otherCheck').attr('disabled')) {
                $('#otherCheck').attr('disabled', 'true');
                $('#otherCheck').removeAttr('checked');
            }
            /**Consignee */
            if (!$('#consigneeCheck').attr('disabled')) {
                $('#consigneeCheck').attr('disabled', 'true');
                $('#consigneeCheck').removeAttr('checked');
            }
            /**Client */
            if (!$('#clientCheck').attr('disabled')) {
                $('#clientCheck').attr('disabled', 'true');
                $('#clientCheck').removeAttr('checked');
            }
            /**Enable shipper */
            if ($('#shipperCheck').attr('disabled')) {
                $('#shipperCheck').removeAttr('disabled');
                $('#shipperCheck').prop('checked', true);
            } else {
                $('#shipperCheck').prop('checked', true);
            }
            /**For Consignee only: check and disable client and shipper and other*/
        } else if (param == "Consignee Only") {
            /**Other */
            if (!$('#otherCheck').attr('disabled')) {
                $('#otherCheck').attr('disabled', 'true');
                $('#otherCheck').removeAttr('checked');
            }
            /**shipper */
            if (!$('#shipperCheck').attr('disabled')) {
                $('#shipperCheck').attr('disabled', 'true');
                $('#shipperCheck').removeAttr('checked');
            }
            /**Client */
            if (!$('#clientCheck').attr('disabled')) {
                $('#clientCheck').removeAttr('checked');
                $('#clientCheck').attr('disabled', 'true');
            }
            /**Enable consignee */
            if (!$('#consigneeCheck').attr('disabled')) {
                $('#consigneeCheck').removeAttr('disabled');
                $('#consigneeCheck').prop('checked', true);
            } else {
                $('#consigneeCheck').removeAttr('disabled');
                $('#consigneeCheck').prop('checked', true);
            }

        } else if (param == 'NoSCMIntergration') {
            /**disable Other */
            if (!$('#otherCheck').attr('disabled')) {
                $('#otherCheck').attr('disabled', 'true');
                $('#otherCheck').prop('checked', false);
                console.log('other');
            } else {
                console.log('other else');
                $('#otherCheck').prop('checked', false);
            }
            /**shipper */
            if (!$('#shipperCheck').attr('disabled')) {
                $('#shipperCheck').attr('disabled', 'true');
                $('#shipperCheck').removeAttr('checked');
            }
            /**Client */
            if (!$('#clientCheck').attr('disabled')) {
                $('#clientCheck').removeAttr('checked');
                $('#clientCheck').attr('disabled', 'true');
            }
            /**consignee */
            if ($('#consigneeCheck').attr('disabled')) {
                $('#consigneeCheck').removeAttr('checked');
            } else {
                $('#consigneeCheck').attr('disabled', 'true');
                $('#consigneeCheck').removeAttr('checked');
            }

        } else {
            /**Enable Other */
            if ($('#otherCheck').attr('disabled')) {
                $('#otherCheck').prop('disabled', false);
                $('#otherCheck').prop('checked', true);
            } else {
                $('#otherCheck').prop('checked', true);
            }
            /**shipper */
            if (!$('#shipperCheck').attr('disabled')) {
                $('#shipperCheck').attr('disabled', 'true');
                $('#shipperCheck').removeAttr('checked');
            }
            /**Client */
            if (!$('#clientCheck').attr('disabled')) {
                $('#clientCheck').removeAttr('checked');
                $('#clientCheck').attr('disabled', 'true');
            }
            /**consignee */
            if ($('#consigneeCheck').attr('disabled')) {
                $('#consigneeCheck').removeAttr('checked');
            } else {
                $('#consigneeCheck').attr('disabled', 'true');
                $('#consigneeCheck').removeAttr('checked');
            }
        }
    }

    // function clientStatus(param) {
    //     /**if client party is the current option */
    //     if (param == "Client with Debtor or Vendor") {
    //         /**client*/
    //         $('#client').prop('disabled', true);
    //         $('#client').removeAttr('required');
    //         $('#clientID').val('');
    //         $('#clientCode').val('');

    //         $('#clientOption').prop('disabled', true);
    //         /**clientOptionVal, requestid and requestcode */
    //         $('#clientOptionVal').val('');
    //         $('#requestID').val('');
    //         $('#requestCode').val('');
    //     } else if (param == "NoSCMIntergration") {
    //         /**client*/
    //         $('#client').prop('disabled', true);
    //         $('#client').removeAttr('required');
    //         $('#clientID').val('');
    //         $('#clientCode').val('');

    //         $('#clientOption').prop('disabled', true);
    //         /**clientOptionVal, requestid and requestcode */
    //         $('#clientOptionVal').val('');
    //         $('#requestID').val('');
    //         $('#requestCode').val('');
    //     }
    //     else if (param == "Create Debtor/Vendor with Existing Client") {
    //         if ($('#client').attr('disabled') && $('#clientOption').attr('disabled')) {
    //             $('#client').prop('disabled', false);
    //             $('#client').attr('required', true);
    //             $('#clientOption').prop('disabled', false);
    //         }
    //     }
    //     else if (param == "Shipper + Consignee") {
    //         if ($('#client').attr('disabled') && $('#clientOption').attr('disabled')) {
    //             $('#client').prop('disabled', false);
    //             $('#client').attr('required', true);
    //             $('#clientOption').prop('disabled', false);
    //         }
    //     } else if (param == "Shipper Only") {
    //         if ($('#client').attr('disabled') && $('#clientOption').attr('disabled')) {
    //             $('#client').prop('disabled', false);
    //             $('#client').attr('required', true);
    //             $('#clientOption').prop('disabled', false);
    //         }
    //     } else if (param == "Consignee Only") {
    //         if ($('#client').attr('disabled') && $('#clientOption').attr('disabled')) {
    //             $('#client').prop('disabled', false);
    //             $('#client').attr('required', true);
    //             $('#clientOption').prop('disabled', false);
    //         }
    //     } else {
    //         $('#client').prop('disabled', true);
    //         $('#clientOption').prop('disabled', true);
    //     }

    // }

    /**
     * the admin details checkboxes.
     */

    /**Client checkbox */
    $('#clientCheck').on('change', function () {
        /**if on do nothing if off open input */
        let cc = $('#clientCheck').is(':checked') == true ? 1 : 0;
        if (cc == 0) {
            $('#clientInput').removeAttr('disabled');
        } else {
            /**Reset client input */
            $('#clientInput').attr('disabled', 'true');
            $('#clientInput').val('');
            /**Reset client id */
            $('#clientID').val('');
            /**Reset client code */
            $('#clientCode').val('');

        }
    });

    /**shipper checkbox */
    $('#shipperCheck').on('change', function () {
        /**if on do nothing if off open input */
        let cc = $('#shipperCheck').is(':checked') == true ? 1 : 0;
        if (cc == 0) {
            $('#shipperInput').removeAttr('disabled');
        } else {
            $('#shipperInput').attr('disabled', 'true');
            $('#shipperInput').val('');
            /**Reset shipper id */
            $('#shipperID').val('');
            /**Reset shipper code */
            $('#shipperCode').val('');
        }
    });

    /**consignee checkbox */
    $('#consigneeCheck').on('change', function () {
        /**if on do nothing if off open input */
        let cc = $('#consigneeCheck').is(':checked') == true ? 1 : 0;
        if (cc == 0) {
            $('#consigneeInput').removeAttr('disabled');
        } else {
            $('#consigneeInput').attr('disabled', 'true');
            $('#consigneeInput').val('');
            /**Reset consignee id */
            $('#consigneeID').val('');
            /**Reset consignee code */
            $('#consigneeCode').val('');
        }
    });

    /**other checkbox */
    $('#otherCheck').on('change', function () {
        /**if on do nothing if off open input */
        let cc = $('#otherCheck').is(':checked') == true ? 1 : 0;
        if (cc == 0) {
            $('#otherInput').removeAttr('disabled');
        } else {
            $('#otherInput').attr('disabled', 'true');
            $('#otherInput').val('');
            /**Reset other id */
            $('#otherID').val('');
            /**Reset other code */
            $('#otherCode').val('');
        }
    });

    let old_office_country_val = $('#officeCountry option:selected').val();
    let old_office_code = $('#officeCountry').find('option:selected').attr('code') ?? '';
    $('#officeCountry').on('change', function () {
        /*let choice = confirm('Changing Country will remove all data associated with it !!');*/
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger mr-1'
            },
            buttonsStyling: false
        })
        if (old_office_country_val != '') {
            swalWithBootstrapButtons.fire({
                title: 'Are you sure?',
                text: "You will loose input data associated to it.!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ok!',
                cancelButtonText: 'Cancel!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#salesPIC').val('');
                    $('#salesPICID').val('');

                    $('#accountPIC').val('');
                    $('#accountPICID').val('');

                    $('#notifyParty1').val('');
                    $('#notifyParty2').val('');

                    $('#notifyParty2ID').val('');
                    $('#notifyParty2Code').val('');

                    $('#notifyParty1ID').val('');
                    $('#notifyParty1Code').val('');

                    $('#forwarderName').val('');
                    $('#forwarderID').val('');
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    $('#officeCountry').val(old_office_country_val);
                }
            });
        } else {
            old_office_country_val = $(this).val();
            old_office_code = $(this).find('option:selected').attr('code');
        }
        /* if (old_office_country_val != '') {
             if (choice) {
                 /*$('#portOfLoadingAir').val('');
                 $('#portOfLoadingAirID').val('');
                 $('#portOfLoadingSea').val('');
                 $('#portOfLoadingSeaID').val('');
                 $('#portOfDischargeAir').val('');
                 $('#portOfDischargeAirID').val('');
                 $('#portOfDischargeSea').val('');
                 $('#portOfDischargeSeaID').val('');

                 $('#salesPIC').val('');
                 $('#salesPICID').val('');

                 $('#accountPIC').val('');
                 $('#accountPICID').val('');

                 $('#notifyParty1').val('');
                 $('#notifyParty2').val('');

                 $('#notifyParty2ID').val('');
                 $('#notifyParty2Code').val('');

                 $('#notifyParty1ID').val('');
                 $('#notifyParty1Code').val('');

                 $('#forwarderName').val('');
                 $('#forwarderID').val('');
             }else{
                 $('#officeCountry').val(old_office_country_val);
             }
         }*/
    });



    let spinner = $('#loader');
    spinner.hide();
    /**/
    $('form').on('submit', function (e) {
        /* e.preventDefault();*/
        spinner.show();
    });

});
