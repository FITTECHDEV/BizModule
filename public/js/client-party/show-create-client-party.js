$(document).ready(function () {
    /*Ajax setup CSRF*/
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    // Function to toggle file upload fieldset based on request type
    function toggleFileUploadFieldset() {
        // Smart type detection for both create and show blades
        let requestType;

        // For show blade: Check if backend type is available
        if (typeof window.getRequestType === "function") {
            requestType = window.getRequestType();
        } else {
            // For create blade: Use URL params or other sources
            requestType =
                new URLSearchParams(window.location.search).get("type") ||
                window.requestType ||
                (typeof type !== "undefined" ? type : null) ||
                document.body.dataset.type ||
                "operational"; // Default fallback
        }

        // Find the file upload fieldset using multiple selectors
        let fileUploadFieldset = $("fieldset").filter(function () {
            return $(this).find("legend").text().trim() === "File Upload";
        });

        // Fallback selector if the above doesn't work
        if (fileUploadFieldset.length === 0) {
            fileUploadFieldset = $(
                'fieldset:has(legend:contains("File Upload"))'
            );
        }

        console.log("Type detected:", requestType);
        console.log("File upload fieldset found:", fileUploadFieldset.length);
        console.log(
            "Context:",
            window.isShowView ? "Show Blade" : "Create Blade"
        );

        if (requestType === "operational") {
            // Hide the entire file upload section
            fileUploadFieldset.hide();

            // Disable all file-related inputs to prevent validation issues
            fileUploadFieldset
                .find('input[type="file"]')
                .prop("disabled", true);
            fileUploadFieldset
                .find('input[name="attachmentNames[]"]')
                .prop("disabled", true);
            fileUploadFieldset
                .find('input[name="attachmentDescription[]"]')
                .prop("disabled", true);
            fileUploadFieldset
                .find(".addAttachment")
                .prop("disabled", true)
                .addClass("disabled");
            fileUploadFieldset
                .find(".deleteFile")
                .prop("disabled", true)
                .addClass("disabled");
            fileUploadFieldset
                .find("button")
                .prop("disabled", true)
                .addClass("disabled");

            console.log("File upload fieldset hidden for operational type");
        } else {
            // Show the file upload section for non-operational types
            fileUploadFieldset.show();

            // Re-enable file inputs (but respect other disabled states)
            if (!window.isShowView || (window.User && window.User.edit)) {
                fileUploadFieldset
                    .find('input[type="file"]')
                    .prop("disabled", false);
                fileUploadFieldset
                    .find('input[name="attachmentNames[]"]')
                    .prop("disabled", false);
                fileUploadFieldset
                    .find('input[name="attachmentDescription[]"]')
                    .prop("disabled", false);
                fileUploadFieldset
                    .find(".addAttachment")
                    .prop("disabled", false)
                    .removeClass("disabled");
                fileUploadFieldset
                    .find(".deleteFile")
                    .prop("disabled", false)
                    .removeClass("disabled");
            }

            console.log("File upload fieldset shown for accounting type");
        }

        // Store globally for other functions
        window.detectedRequestType = requestType;
        return requestType;
    }

    /* Global Variable declaration and initialization*/
    let office = "";
    let officeID = "";
    let officeCode = "";
    let includeCountry = "";
    let countryCode = "";
    let includeCode = "";

    /*Initialize Additional Information section*/
    $("#tarrif").attr("disabled", "true");
    $("#fscType").attr("disabled", "true");
    $("#fscRate").attr("disabled", "true");
    $("#billingType").attr("disabled", "true");
    $("#discount").attr("disabled", "true");

    /**Align credit limit and credit days*/
    $('[name^="maxCreditLimit"]').css("text-align", "right");
    $('[name^="creditDays"]').css("text-align", "right");
    $('[name^="fscRate"]').css("text-align", "right");
    $('[name^="discount"]').css("text-align", "right");
    /**Format credit limit*/
    $("#maxCreditLimit").number(true, 2);

    $("#licenceTypeID").on("change", function () {
        let code = $("#licenceTypeID option:selected").attr("id");
        $("#licenceTypeCode").val(code);
    });

    /*User role */
    if (window.User.value != 1 || window.User.edit == false) {
        $("#clientPartyForm :input")
            .not("[name=next], [name=previous]")
            .not(
                "#admin-details :input, #otherInput, #debtor, #vendor, #otherCheck2, #debtorCheck, #vendorCheck, #otherID, #otherCode, #debtorID, #debtorCode, #vendorID, #vendorCode"
            )
            .prop("disabled", true);
    }

    /* Ensure #admin-details fields are enabled in show view */
    if (window.isShowView) {
        $(
            "#admin-details :input, #otherInput, #debtor, #vendor, #otherCheck2, #debtorCheck, #vendorCheck, #otherID, #otherCode, #debtorID, #debtorCode, #vendorID, #vendorCode"
        )
            .prop("disabled", false)
            .prop("readonly", false);
    }

    /*approval*/
    if (window.approve_client_party == 1) {
        $("#approveBtn").prop("disabled", false);
        $("#rejectBtn").prop("disabled", false);
    }

    // Initialize file upload toggle
    toggleFileUploadFieldset();

    // Re-run on stepper change or other events
    $(document).on("shown.bs-stepper", ".bs-stepper", function () {
        toggleFileUploadFieldset();
    });

    // Also call when partyEntityType changes (for create blade)
    $("#partyEntityType").on("change", function () {
        setTimeout(toggleFileUploadFieldset, 100); // Small delay to ensure other handlers run first
    });

    /** Number input requied field validator*/
    let regExp = new RegExp("[a-zA-Z]"),
        inputVal = "";
    $(".number_input").on("keydown keyup", function (e) {
        let value = $(this).val();
        /* Do not allow alphabets to be entered.*/
        if (regExp.test(value)) {
            $(this).val(inputVal);
        } else {
            inputVal = value;
        }
    });

    /**Default country code */
    let default_code = $("#countryID option:selected").attr("id");
    includeCode = default_code;
    /*Country on change event listener*/
    $("#countryID").on("change", function () {
        let code = $("#countryID option:selected").attr("id");
        let name = $("#countryID option:selected").text();
        /*set the country name inputs*/
        $("#countryName").val(name);
        /*set the country code inputs*/
        $("#countryCode").val(code);
        /*Update the global country code var*/
        includeCode = code;
    });

    /*Incoterm on change listener*/
    $("#incoTermID").on("change", function () {
        let name = $("#incoTermID option:selected").text();
        let code = $("#incoTermID option:selected").attr("id");
        /**console.log(code);
        Set the name and the code*/
        $("#incoTermName").val(name);
        $("#incoTermCode").val(code);
    });

    /*payment on change listener*/
    $("#paymentTermsID").on("change", function () {
        let name = $("#paymentTermsID  option:selected").text();
        let code = $("#paymentTermsID  option:selected").attr("id");
        /**Set the name and the code*/
        $("#paymentTermsName").val(name);
        $("#paymentTermsCode").val(code);
    });

    /*Auto complete cities based on the country*/
    $("#cityName").autocomplete({
        source: function (request, response) {
            return $.get(
                "/get-cities",
                {
                    country_code: $("#countryCode").val() /*Country code*/,
                    query: request.term,
                    _token: "{{ csrf_token() }}",
                },
                function (data) {
                    response(data);
                }
            );
        },
        select: function (event, ui) {
            /* Set selection*/
            $("#cityName").val(ui.item.label); /* display the selected text*/
            $("#CityID").val(ui.item.value);
            $("#cityCode").val(ui.item.code);
            return false;
        },
        focus: function (event, ui) {
            $("#cityName").val(ui.item.label); /* display the selected text*/
            $("#cityID").val(ui.item.value);
            $("#cityCode").val(ui.item.code);
            return false;
        },
    });

    /*Autocomplete search provines/cities*/
    $("#provinceName").autocomplete({
        source: function (request, response) {
            return $.get(
                "/get-provinces",
                {
                    country_code: $("#countryCode").val() /*Country code*/,
                    query: request.term,
                    _token: "{{ csrf_token() }}",
                },
                function (data) {
                    response(data);
                }
            );
        },
        select: function (event, ui) {
            /* Set selection*/
            $("#provinceName").val(
                ui.item.label
            ); /* display the selected text*/
            $("#provinceID").val(ui.item.value);
            $("#provinceCode").val(ui.item.code);
            return false;
        },
        focus: function (event, ui) {
            $("#provinceName").val(
                ui.item.label
            ); /* display the selected text*/
            $("#provinceID").val(ui.item.value);
            $("#provinceCode").val(ui.item.code);
            return false;
        },
    });

    /** If office country is set for default users*/
    let office_country_default_val = $("#officeCountryDisp").val();
    let office_country_default_code = $("#officeCountryDisp").attr("code");
    let office_country_default_id = $("#officeCountryID").val()
        ? $("#officeCountryID").val()
        : $("#officeCountryID option:selected").val();
    if (office_country_default_val != null) {
        /**set the office*/
        office = office_country_default_val;
        officeID = office_country_default_id;
        officeCode = office_country_default_code;
        /*Currency list*/
        currencyList(officeID);
        getDebtorVendorList(officeCode);
        /*tarrif list
        tarrif(officeID);*/
        /*Base currency*/
        getBaseCurrency(officeID);
    }
    /**Office country selected for admin users */
    let oc_admin_id = $("#officeCountry option:selected").attr("id");
    let oc_admin_code = $("#officeCountry option:selected").attr("code");
    let oc_admin_val = $("#officeCountry option:selected").val();
    oc_admin_code =
        oc_admin_code == "undefined" || !oc_admin_code
            ? $("#officeCountryCode").val()
            : oc_admin_code;

    if (oc_admin_id != null) {
        officeID = oc_admin_id;
        showAdditionalInformation(oc_admin_val);
        getDebtorVendorList(oc_admin_code); /**/
        if (!window.User.edit) {
            /*disable additional info*/
            $("#tarrif").prop("disabled", "true");
            $("#fscType").prop("disabled", "true");
            $("#fscRate").prop("disabled", "true");
            $("#billingType").prop("disabled", "true");
            $("#discount").prop("disabled", "true");
        }
    }
    /*show office country*/

    /*Office on change operation*/
    $("#officeCountry").on("change", function () {
        office = $(this).val();
        officeID = $("#officeCountry option:selected").attr("id");
        officeCode = $("#officeCountry option:selected").attr("code");
        $("#clientPartyForm").append(
            '<input type="hidden" name="officeCode" value=' + officeCode + ">"
        );
        /* Set officecountry id*/
        $("#officeCountryID").val(officeID);

        /*set base currency*/
        getBaseCurrency(officeID);

        /* Get debtor vendor list*/
        getDebtorVendorList(officeCode);

        /* set country code if include country is checked*/
        countryCode = includeCountry == 1 ? $("#countryCode").val() : null;
        /*Currency list*/
        currencyList(officeID);
        showAdditionalInformation(office);
    });

    function showAdditionalInformation(param) {
        /*Show additional information*/
        if (param == "FIT - Kenya - UPS") {
            /*tarrif list
            tarrif(officeID);*/
            $("#tarrif").removeAttr("disabled");
            $("#fscType").removeAttr("disabled");
            $("#fscRate").removeAttr("disabled");
            $("#billingType").removeAttr("disabled");
            $("#discount").removeAttr("disabled");
        } else {
            $("#tarrif").val("").attr("disabled", "true");
            $("#fscType").val("").attr("disabled", "true");
            $("#fscRate").val("").attr("disabled", "true");
            $("#billingType").val("").attr("disabled", "true");
            $("#discount").val("").attr("disabled", "true");
        }
    }

    /*Search auto vendor*/
    $("#vendor").autocomplete({
        source: function (request, response) {
            return $.get(
                "/get-debtor-vendor/Vendor",
                {
                    officeID: officeID,
                    query: request.term,
                    _token: "{{ csrf_token() }}",
                },
                function (data) {
                    response(data);
                }
            );
        },
        select: function (event, ui) {
            /* Set selection*/
            $("#vendor").val(ui.item.label); /* display the selected text*/
            $("#vendorID").val(ui.item.value);
            $("#vendorCode").val(ui.item.code);
            $("#vendor_code").html(ui.item.code);

            return false;
        },
        focus: function (event, ui) {
            $("#vendor").val(ui.item.label); /* display the selected text*/
            $("#vendorID").val(ui.item.value);
            $("#vendorCode").val(ui.item.code);
            $("#vendor_code").html(ui.item.code);

            return false;
        },
    });

    /*Search auto debtor*/
    $("#debtor").autocomplete({
        source: function (request, response) {
            return $.get(
                "/get-debtor-vendor/Debtor",
                {
                    officeID: officeID,
                    query: request.term,
                    _token: "{{ csrf_token() }}",
                },
                function (data) {
                    response(data);
                }
            );
        },
        select: function (event, ui) {
            /* Set selection*/
            $("#debtor").val(ui.item.label); /* display the selected text*/
            $("#debtorID").val(ui.item.value);
            $("#debtorCode").val(ui.item.code);
            $("#debtor_code").html(ui.item.code);
            /*$('#debtor_code').append('<br><small>'+ui.item.label+'</small>');*/
            return false;
        },
        /*focus: function (event, ui) {
            $('#debtor').val(ui.item.label);  display the selected text
            $('#debtorID').val(ui.item.value);
            $('#debtorCode').val(ui.item.code);
            $('#debtor_code').html(ui.item.code);*/
        /*$('#debtor_code').append('<br><small>'+ui.item.label+'</small>');
            return false;
        },*/
    });

    /*Search auto shipper input*/
    $("#shipperInput").autocomplete({
        source: function (request, response) {
            return $.get(
                "/get-party-entity",
                {
                    officeID: officeID,
                    query: request.term,
                    partyType: "Shipper",
                    _token: "{{ csrf_token() }}",
                },
                function (data) {
                    response(data);
                }
            );
        },
        select: function (event, ui) {
            /* Set selection*/
            $("#shipperInput").val(
                ui.item.label
            ); /* display the selected text*/
            $("#shipperID").val(ui.item.value);
            $("#shipperCode").val(ui.item.code);
            $("#shipper_Input").html(ui.item.code);
            return false;
        },
        focus: function (event, ui) {
            $("#shipperInput").val(
                ui.item.label
            ); /* display the selected text*/
            $("#shipperID").val(ui.item.value);
            $("#shipperCode").val(ui.item.code);
            $("#shipper_Input").html(ui.item.code);
            return false;
        },
    });
    /*Search auto notify party1*/
    $("#notifyParty1").autocomplete({
        source: function (request, response) {
            return $.get(
                "/get-party-entity",
                {
                    officeID: officeID,
                    query: request.term,
                    partyType: "Notify Party",
                    _token: "{{ csrf_token() }}",
                },
                function (data) {
                    response(data);
                }
            );
        },
        select: function (event, ui) {
            /* Set selection*/
            $("#notifyParty1").val(
                ui.item.label
            ); /* display the selected text*/
            $("#notifyParty1ID").val(ui.item.value);
            $("#notifyParty1Code").val(ui.item.code);
            return false;
        },
        focus: function (event, ui) {
            $("#notifyParty1").val(
                ui.item.label
            ); /* display the selected text*/
            $("#notifyParty1ID").val(ui.item.value);
            $("#notifyParty1Code").val(ui.item.code);
            return false;
        },
    });
    /*Search auto notify party1*/
    $("#notifyParty2").autocomplete({
        source: function (request, response) {
            return $.get(
                "/get-party-entity",
                {
                    officeID: officeID,
                    query: request.term,
                    partyType: "Notify Party",
                    _token: "{{ csrf_token() }}",
                },
                function (data) {
                    response(data);
                }
            );
        },
        select: function (event, ui) {
            /* Set selection*/
            $("#notifyParty2").val(
                ui.item.label
            ); /* display the selected text*/
            $("#notifyParty2ID").val(ui.item.value);
            $("#notifyParty2Code").val(ui.item.code);
            return false;
        },
        focus: function (event, ui) {
            $("#notifyParty2").val(
                ui.item.label
            ); /* display the selected text*/
            $("#notifyParty2ID").val(ui.item.value);
            $("#notifyParty2Code").val(ui.item.code);
            return false;
        },
    });

    /*Search auto otherinput */
    $("#otherInput")
        .autocomplete({
            source: function (request, response) {
                // Normalize entityType to match loadCompanies
                let entityType =
                    $("#partyEntityType option:selected").val() || "";
                const typeMapping = {
                    "Agent / Forwarder": "Forwarder",
                    ShippingLine: "Shipping Line",
                    Trucker: "Trucker / Transporter",
                    Airline: "Airline",
                    Client: "Client",
                    CreateonlyinD365: "Create only in D365",
                };
                const normalizedType = entityType
                    .replace(/\s*\/\s*/g, "/")
                    .replace(/\s+/g, "");
                entityType = typeMapping[normalizedType] || entityType;

                if (!entityType) {
                    response([
                        { label: "Please select a party type", value: "" },
                    ]);
                    return;
                }

                console.log("otherInput autocomplete AJAX:", {
                    entityType,
                    query: request.term,
                });

                $.ajax({
                    url: "/api/companies", // Use same endpoint as loadCompanies
                    type: "GET",
                    data: {
                        entityType: entityType,
                        query: request.term, // Send query for server-side filtering
                    },
                    success: function (data) {
                        console.log(
                            "otherInput autocomplete response:",
                            JSON.stringify(data, null, 2)
                        );

                        if (data.error) {
                            response([{ label: data.error, value: "" }]);
                            return;
                        }

                        if (!Array.isArray(data) || data.length === 0) {
                            response([
                                { label: "No entities found", value: "" },
                            ]);
                            return;
                        }

                        // ✅ Limit to 15 suggestions before mapping
                        const limited = data.slice(0, 15);

                        const mappedData = limited.map((entity) => ({
                            label: `${entity.name} (${entity.code})`,
                            value: entity.id,
                            code: entity.code,
                            name: entity.name,
                        }));

                        response(mappedData);
                    },

                    error: function (xhr, status, error) {
                        console.error(
                            "otherInput autocomplete error:",
                            status,
                            error
                        );
                        response([
                            { label: "Error loading suggestions", value: "" },
                        ]);
                    },
                });
            },
            select: function (event, ui) {
                $("#otherInput").val(ui.item.name); // Use name for input display
                $("#otherID").val(ui.item.value);
                $("#otherCode").val(ui.item.code);
                $("#other_Input").html(ui.item.code);
                return false;
            },
            focus: function (event, ui) {
                $("#otherInput").val(ui.item.name); // Use name on focus
                $("#otherID").val(ui.item.value);
                $("#otherCode").val(ui.item.code);
                $("#other_Input").html(ui.item.code);
                return false;
            },
            minLength: 0, // Allow empty query to show all results
        })
        .on("click", function () {
            // Show full list on click
            if (!$(this).val()) {
                $(this).autocomplete("search", "");
            }
        });

    /*Search auto vendor*/
    $("#consigneeInput").autocomplete({
        source: function (request, response) {
            return $.get(
                "/get-party-entity",
                {
                    officeID: officeID,
                    query: request.term,
                    partyType: "Consignee",
                    _token: "{{ csrf_token() }}",
                },
                function (data) {
                    response(data);
                }
            );
        },
        select: function (event, ui) {
            /* Set selection*/
            $("#consigneeInput").val(
                ui.item.label
            ); /* display the selected text*/
            $("#consigneeID").val(ui.item.value);
            $("#consigneeCode").val(ui.item.code);
            $("#consignee_Input").html(ui.item.code);
            return false;
        },
        focus: function (event, ui) {
            $("#consigneeInput").val(
                ui.item.label
            ); /* display the selected text*/
            $("#consigneeID").val(ui.item.value);
            $("#consigneeCode").val(ui.item.code);
            $("#consignee_Input").html(ui.item.code);
            return false;
        },
    });

    /**ClientInput Admin details  Autocomplete*/
    $("#clientInput").autocomplete({
        source: function (request, response) {
            return $.get(
                "/get-clients/master",
                {
                    query: request.term,
                    _token: "{{ csrf_token() }}",
                },
                function (data) {
                    response(data);
                }
            );
        },
        select: function (event, ui) {
            /* Set selection*/
            $("#clientInput").val(ui.item.label); /* display the selected text*/
            $("#clientID").val(ui.item.value);
            $("#clientCode").val(ui.item.code);
            $("#client_Input").html(ui.item.code);
            return false;
        },
        focus: function (event, ui) {
            $("#clientInput").val(ui.item.label); /* display the selected text*/
            $("#clientID").val(ui.item.value);
            $("#clientCode").val(ui.item.code);
            $("#client_Input").html(ui.item.code);
            return false;
        },
    });

    /**Client Autocomplete*/
    $("#client").autocomplete({
        source: function (request, response) {
            return $.get(
                "/get-clients/master",
                {
                    query: request.term,
                    _token: "{{ csrf_token() }}",
                },
                function (data) {
                    response(data);
                }
            );
        },
        select: function (event, ui) {
            /* Set selection*/
            $("#client").prop(
                "value",
                ui.item.label
            ); /* display the selected text*/
            $("#clientID").val(ui.item.value);
            $("#clientCode").val(ui.item.code);
            /*if user not admin append inputs with values*/
            if (window.User.value == 1) {
                $("#clientID").remove();
                $("#clientCode").remove();
                $("#clientPartyForm").append(
                    '<input type="hidden" name="clientID" value="' +
                        ui.item.value +
                        '">'
                );
                $("#clientPartyForm").append(
                    '<input type="hidden" name="clientCode" value="' +
                        ui.item.code +
                        '">'
                );
            }

            return false;
        },
        focus: function (event, ui) {
            /*$('#client').val(ui.item.label);  display the selected text
            $('#clientID').val(ui.item.value);
            $('#clientCode').val(ui.item.code);*/
            return false;
        },
    });

    $("#clientOptionVal").hide();
    /**If clientOptionval is not empty show */
    if ($("#clientOptionVal").val() != "") {
        $("#clientOptionVal").removeAttr("style");
    }

    /*clientOption change function */
    $("#clientOption").on("change", function () {
        const entities = [
            "Shipper + Consignee",
            "Consignee Only",
            "Shipper Only",
            "NoSCMIntergration",
        ];
        var criteria = entities.includes($("#partyEntityType").val());

        /* Change the client id and name to clientOptionVal  */
        if ($(this).val() == "fromRequest") {
            $("#client").hide();
            $("#client").val("");
            $("#clientID").val("");
            $("#clientCode").val("");

            $("#client").removeClass("is-invalid");
            $("#client").prop("required", false);

            /*Show clientOptionVal input*/
            $("#clientOptionVal").show();
            $("#clientOptionVal").prop("required", true);
            if (criteria) {
                $("#clientOptionVal").prop("disabled", false);
            }
        } else if ($(this).val() == "fromMaster") {
            $("#clientOptionVal").hide();
            $("#clientOptionVal").val("");
            $("#clientOptionVal").prop("required", false);

            /*Show client input*/
            $("#client").show();
            $("#client").prop("required", true);
            if (criteria) {
                $("#client").prop("disabled", false);
            }
        }
    });

    /* Autosearch clientOptionVal */
    $("#clientOptionVal").autocomplete({
        source: function (request, response) {
            return $.get(
                "/get-clients/local",
                {
                    query: request.term,
                    _token: "{{ csrf_token() }}",
                },
                function (data) {
                    response(data);
                }
            );
        },
        select: function (event, ui) {
            $("#clientOptionVal").prop(
                "value",
                ui.item.label
            ); /* display the selected text*/
            $("#requestID").val(ui.item.value);
            $("#requestCode").val(
                ui.item.code
            ); /* save selected code to input*/
            return false;
        },
        focus: function (event, ui) {
            $("#clientOptionVal").prop(
                "value",
                ui.item.label
            ); /* display the selected text*/
            $("#requestID").val(ui.item.value);
            $("#requestCode").val(
                ui.item.code
            ); /* save selected code to input*/
            return false;
        },
    });

    /**Include country change function*/
    $("#includeCountryCheckBox").on("change", function () {
        /** set includeCountry state*/
        includeCountry = $(this).is(":checked") == true ? 1 : 0;
    });

    /*Search auto port of loading*/
    $("#portOfLoadingAir").autocomplete({
        /*delay: 250,*/
        source: function (request, response) {
            return $.get(
                "/get-plo-pld/Air",
                {
                    query: request.term,
                    office: office,
                    includeCode: includeCode,
                    includeCountry: includeCountry,
                    _token: "{{ csrf_token() }}",
                },
                function (data) {
                    response(data);
                }
            );
        },
        select: function (event, ui) {
            /* Set selection*/
            $("#portOfLoadingAir").val(
                ui.item.label
            ); /* display the selected text*/
            $("#portOfLoadingAirID").val(ui.item.value);
            return false;
        },
        focus: function (event, ui) {
            $("#portOfLoadingAir").val(ui.item.label);
            $("#portOfLoadingAirID").val(ui.item.value);
            return false;
        },
    });

    /*Search auto port of loading sea*/
    $("#portOfLoadingSea").autocomplete({
        /* override default values here
        minLength: 2,*/
        /*delay: 250,*/
        source: function (request, response) {
            return $.get(
                "/get-plo-pld/Sea",
                {
                    query: request.term,
                    office: office,
                    includeCode: includeCode,
                    includeCountry: includeCountry,
                    _token: "{{ csrf_token() }}",
                },
                function (data) {
                    response(data);
                }
            );
        },
        select: function (event, ui) {
            /* Set selection*/
            $("#portOfLoadingSea").val(
                ui.item.label
            ); /* display the selected text*/
            $("#portOfLoadingSeaID").val(ui.item.value);
            return false;
        },
        focus: function (event, ui) {
            $("#portOfLoadingSea").val(ui.item.label);
            $("#portOfLoadingSeaID").val(ui.item.value);
            return false;
        },
    });
    /*Search auto PIC discharge*/
    $("#portOfDischargeAir").autocomplete({
        /* minLength: 2,
        delay: 250,*/
        source: function (request, response) {
            return $.get(
                "/get-plo-pld/Air",
                {
                    query: request.term,
                    office: office,
                    includeCode: includeCode,
                    includeCountry: includeCountry,
                    _token: "{{ csrf_token() }}",
                },
                function (data) {
                    response(data);
                }
            );
        },
        select: function (event, ui) {
            /* Set selection*/
            $("#portOfDischargeAir").val(
                ui.item.label
            ); /* display the selected text*/
            $("#portOfDischargeAirID").val(ui.item.value);
            return false;
        },
        focus: function (event, ui) {
            $("#portOfDischargeAir").val(ui.item.label);
            $("#portOfDischargeAirID").val(ui.item.value);
            return false;
        },
    });
    /*Search auto PIC discharge*/
    $("#portOfDischargeSea").autocomplete({
        minLength: 2,
        /*delay: 250,*/
        source: function (request, response) {
            return $.get(
                "/get-plo-pld/Sea",
                {
                    query: request.term,
                    office: office,
                    includeCode: includeCode,
                    includeCountry: includeCountry,
                    _token: "{{ csrf_token() }}",
                },
                function (data) {
                    response(data);
                }
            );
        },
        select: function (event, ui) {
            /* Set selection*/
            $("#portOfDischargeSea").val(
                ui.item.label
            ); /* display the selected text*/
            $("#portOfDischargeSeaID").val(ui.item.value);
            return false;
        },
        focus: function (event, ui) {
            $("#portOfDischargeSea").val(ui.item.label);
            $("#portOfDischargeSeaID").val(ui.item.value);
            return false;
        },
    });
    /*Search sales PIC*/
    $("#salesPIC").autocomplete({
        /*minLength: 2,*/
        source: function (request, response) {
            /* let office = $('#officeCountry').val();*/
            return $.get(
                "/get-sales-account-pic/S",
                {
                    query: request.term,
                    officeID: officeID,
                    _token: "{{ csrf_token() }}",
                },
                function (data) {
                    response(data);
                }
            );
        },
        select: function (event, ui) {
            /* Set selection*/
            $("#salesPIC").val(ui.item.label); /* display the selected text*/
            $("#salesPICID").val(ui.item.value);
            return false;
        },
        focus: function (event, ui) {
            $("#salesPIC").val(ui.item.label);
            $("#salesPICID").val(ui.item.value); /**/
            return false;
        },
    });
    /*Search account PIC*/
    $("#accountPIC").autocomplete({
        /*minLength:2,*/
        source: function (request, response) {
            /*let office = $('#officeCountry').val();*/
            return $.get(
                "/get-sales-account-pic/A",
                {
                    query: request.term,
                    officeID: officeID,
                    _token: "{{ csrf_token() }}",
                },
                function (data) {
                    response(data);
                }
            );
        },
        select: function (event, ui) {
            /* Set selection*/
            $("#accountPIC").val(ui.item.label); /* display the selected text*/
            $("#accountPICID").val(ui.item.value); /*save selected id to input*/
            return false;
        },
        focus: function (event, ui) {
            $("#accountPIC").val(ui.item.label);
            $("#accountPICID").val(ui.item.value); /**/
            return false;
        },
    });
    /*Search forwarder*/
    $("#forwarderName").autocomplete({
        minLength: 2,
        source: function (request, response) {
            /*let office = $('#officeCountry').val();*/
            return $.get(
                "/get-notify-party-list/Forwarder",
                {
                    query: request.term,
                    officeID: officeID,
                    _token: "{{ csrf_token() }}",
                },
                function (data) {
                    response(data);
                }
            );
        },
        select: function (event, ui) {
            /* Set selection*/
            $("#forwarderName").val(
                ui.item.label
            ); /* display the selected text*/
            $("#forwarderID").val(ui.item.value); /*save selected id to input*/
            return false;
        },
        focus: function (event, ui) {
            $("#forwarderName").val(ui.item.label);
            $("#forwarderID").val(ui.item.value); /**/
            return false;
        },
    });

    /*Get get base currency List*/
    function getBaseCurrency(officeid) {
        $.ajax({
            url: "/get-base-currency",
            type: "get",
            data: {
                query: officeid,
                _token: "{{ csrf_token() }}",
            },
            dataType: "json",
            success: function (response) {
                $("#baseCurrency").val(response[0]["code"]);
                $("#baseCurrencyCode").val(response[0]["code"]);
                $("#baseCurrencyID").val(response[0]["value"]);
                $("#baseCurrencyName").val(response[0]["label"]);
            },
        });
    }

    function getDebtorVendorList(office_code) {
        $.ajax({
            url: "/get-office-debtor-vendor-list",
            type: "get",
            data: {
                query: office_code,
                _token: "{{ csrf_token() }}",
            },
            dataType: "json",
            success: function (response) {
                /** set the debtors #debtorType*/
                $("#debtorType").find("option").not(":first").remove();
                $("#vendorType").find("option").not(":first").remove();

                response.Vendors.forEach((vendor) => {
                    let vendor_sel =
                        window.default_vendor == vendor ? "selected" : "";
                    $("#vendorType").append(
                        "<option value=" +
                            vendor +
                            " " +
                            vendor_sel +
                            ">" +
                            vendor +
                            "</option>"
                    );
                });
                response.Debtors.forEach((debtor) => {
                    // console.log((window.default_debtor));
                    let debtor_sel =
                        window.default_debtor == debtor ? "selected" : "";
                    $("#debtorType").append(
                        "<option value=" +
                            debtor +
                            " " +
                            debtor_sel +
                            ">" +
                            debtor +
                            "</option>"
                    );
                });
                // set the vendors #vendorType
            },
        });
    }

    /*Get Currency List*/
    function currencyList(param) {
        $.ajax({
            url: "/get-currency-list",
            type: "get",
            data: {
                query: param,
                _token: "{{ csrf_token() }}",
            },
            dataType: "json",
            success: function (response) {
                $("#billingCurrency option:not(:first)").remove();
                let len = response.length;
                for (let i = 0; i < len; i++) {
                    let currencyCode = response[i]["CurrencyCode"];
                    let currencyID = response[i]["CurrencyID"];
                    let sel =
                        window.default_bill_curr != "" &&
                        window.default_bill_curr == currencyID
                            ? "selected"
                            : "";
                    $("#billingCurrency").append(
                        '<option value="' +
                            currencyID +
                            '" id="' +
                            currencyCode +
                            '" ' +
                            sel +
                            ">" +
                            currencyCode +
                            "</option>"
                    );
                }
            },
        });
    }

    /*get tarrif list
    function tarrif(param) {
        $.ajax({
            url: '/get-tarrif-list',
            type: 'get',
            data: {
                query: param,
                "_token": "{{ csrf_token() }}",
            },
            dataType: 'json',
            success: function (response) {
                $('#tarrif option:not(:first)').remove();
                let len = response.length;
                for (let i = 0; i < len; i++) {
                    let tarrif = response[i]['TARIFF'];
                    $("#tarrif").append('<option value="' + tarrif + '">' + tarrif + '</option>');
                }
            }
        });
    }*/
    /*Billing currency change function*/
    $("#billingCurrency").on("change", function () {
        let code = $("#billingCurrency option:selected").attr("id");
        $("#billingCurrencyCode").val(code);
    });

    /*Vendor licence change function*/
    $("#vendorLicenceType").on("change", function () {
        let code = $("#vendorLicenceType option:selected").attr("id");
        $("#vendorLicenceCode").val(code);
    });

    /*Debtor licence change functioin*/
    $("#debtorLicenceType").on("change", function () {
        let code = $("#debtorLicenceType option:selected").attr("id");
        $("#debtorLicenceCode").val(code);
    });

    /* BS-Stepper Init*/
    window.stepper = new Stepper($(".bs-stepper")[0], {
        linear: false,
        animation: true,
    });

    // // NEW: Force open tab 1
    // setTimeout(function() {
    //     if (window.stepper) {
    //         window.stepper.to(0);
    //         console.log('🔧 Stepper forced to tab 1 (Basic Details)');

    //         // Ensure navigation buttons are clickable
    //         $('button[name="next"], button[name="previous"]').prop('disabled', false)
    //             .removeClass('disabled')
    //             .css('pointer-events', 'auto');
    //     }
    // }, 500);
});
