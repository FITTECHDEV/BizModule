       /** submit button click */
       $("submitBtn").on('click', function(e){
        let sub = false;
        let office_country_bool = $('#officeCountry option:selected').val() == '';
        /** verify office country*/
        if (office_country_bool) {
            $('#officeCountry').addClass('is-invalid');
            e.preventDefault();
            sub = false;
        }else{
            $('#officeCountry').removeClass('is-invalid');
            sub = true;
        }
        /** Verify sales pic*/
        let sales_pic_bool =  $('#salesPIC').val() == '';
        if (sales_pic_bool) {
            $('#salesPIC').addClass('is-invalid');
            e.preventDefault();
            sub = false;
        }else{
            $('#salesPIC').removeClass('is-invalid');
            sub = true;
        }

        return sub;

    });
