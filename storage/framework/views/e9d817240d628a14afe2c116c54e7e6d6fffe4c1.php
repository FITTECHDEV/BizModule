<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content_header'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <!-- Preloader -->
    <div class="preloader">
        <div class="preloader-inner">
            <div class="preloader-icon">
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
    <!-- End Preloader -->
    <div class="col-md-12">
        <div class="card card-grey mt-2">
            
            <div class="card-body p-0">
                <div class="bs-stepper">
                    <div class="row justify-content-center">
                        <div class="bs-stepper-header col-md-10" role="tablist">
                            <!-- your steps here -->
                            <div class="step" data-target="#basic-details">
                                <button type="button" class="step-trigger" role="tab" aria-controls="basic-details"
                                    id="basic-details-trigger" aria-selected="true">
                                    <span class="bs-stepper-circle">1</span>
                                    <span class="bs-stepper-label">Basic Details</span>
                                </button>
                            </div>
                            <div class="line"></div>
                            <div class="step" data-target="#office-details">
                                <button type="button" class="step-trigger" role="tab" aria-controls="office-details"
                                    id="office-details-trigger" aria-selected="true">
                                    <span class="bs-stepper-circle">2</span>
                                    <span class="bs-stepper-label">Office Details</span>
                                </button>
                            </div>
                            <?php if(Auth::user()->can('create-client-party')): ?>
                            <div class="line"></div>
                            <div class="step" data-target="#admin-details">
                                <button type="button" class="step-trigger" role="tab" aria-controls="office-details"
                                    id="admin-details-trigger" aria-selected="true">
                                    <span class="bs-stepper-circle">3</span>
                                    <span class="bs-stepper-label">Admin Details</span>
                                </button>
                            </div>
                            <?php endif; ?>
                            <div class="">
                                <a href="/client-party" class="bs-stepper-label btn btn-outline-primary">
                                    <i class="fa fa-home"></i> Home
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="bs-stepper-content container">
                        <form action="<?php echo e(route('client-party.update',[$clientParty->Id])); ?>" method="POST" class="form-horizontal"
                            enctype="multipart/form-data" id="clientPartyForm">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('put'); ?>
                            <!-- your steps content here -->
                            <div id="basic-details" class="content" role="tabpanel"
                                aria-labelledby="basic-details-trigger">
                                <fieldset class="border pl-2 pr-2 card shadow-sm m-0" style="border-radius: 10px;">
                                    <legend class="w-auto p-0 m-0">Entity Creation Options</legend>
                                    <div class="row">
                                        <div class="col col-md-6 col-sm-12">
                                            <div class="form-group row mb-1">
                                                <label for="partyEntityType"
                                                    class="col-sm-3 col-lg-4 col-md-4 col-form-label col-form-label-sm"
                                                    style="font-size: 17px">Operation Type<span
                                                        class="text-danger">*</span></label>
                                                <div class="col-sm-9 col-lg-8 col-md-8">
                                                    
                                                    <select class="form-control form-control-sm" id="partyEntityType"
                                                        name="partyEntityType" required>
                                                        <option value="" disabled selected></option>
                                                        <option value="Client + Shipper + Consignee"
                                                            <?php if(old('partyEntityType',$clientParty->OperatioEntityType)
                                                            == 'Client + Shipper + Consignee'): ?> selected <?php endif; ?>>Client +
                                                            Shipper + Consignee</option>
                                                        
                                                        <option value="Shipper + Consignee"
                                                            <?php if(old('partyEntityType',$clientParty->OperatioEntityType)
                                                            == 'Shipper + Consignee'): ?> selected <?php endif; ?>>Shipper +
                                                            Consignee</option>
                                                        <option value="Shipper Only"
                                                            <?php if(old('partyEntityType',$clientParty->OperatioEntityType)
                                                            == 'Shipper Only'): ?> selected <?php endif; ?>>Shipper Only</option>
                                                        <option value="Consignee Only"
                                                            <?php if(old('partyEntityType',$clientParty->OperatioEntityType)
                                                            == 'Consignee Only'): ?> selected <?php endif; ?>>Consignee Only</option>
                                                        <option value="Forwarder"
                                                            <?php if(old('partyEntityType',$clientParty->OperatioEntityType)
                                                            == 'Forwarder'): ?> selected <?php endif; ?>>Agent / Forwarder</option>
                                                        <option value="Airline" <?php if(old('partyEntityType',$clientParty->
                                                            OperatioEntityType) == 'Airline'): ?> selected <?php endif; ?>>Airline
                                                        </option>
                                                        <option value="Shipping Line"
                                                            <?php if(old('partyEntityType',$clientParty->OperatioEntityType)
                                                            == 'Shipping Line'): ?> selected <?php endif; ?>>Shipping Line</option>
                                                        <option value="Trucker" <?php if(old('partyEntityType',$clientParty->
                                                            OperatioEntityType) == 'Trucker'): ?> selected <?php endif; ?>>Trucker /
                                                            Transportor</option>
                                                        <option value="NoSCMIntergration" <?php if(old('partyEntityType',
                                                            $clientParty->OperatioEntityType)=='NoSCMIntergration' ): ?>
                                                            selected <?php endif; ?>>Debtor/Vendor In D365</option>

                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col col-md-6 col-sm-12">
                                            <div class="form-group row mb-1">
                                                <label for="legalEntityType"
                                                    class="col-sm-3 col-lg-4 col-md-4 col-form-label col-form-label-sm"
                                                    style="font-size: 17px">Legal Type <span
                                                        class="text-danger">*</span></label>
                                                <div class="col-sm-9 col-lg-8 col-md-8">
                                                    <select class="form-control form-control-sm" id="legalEntityType"
                                                        name="legalEntityType">
                                                        <option value="" disabled selected></option>
                                                        <option value="Debtor & Vendor"
                                                            <?php if(old('legalEntityType',$clientParty->LegalEntityType) ==
                                                            'Debtor & Vendor'): ?> selected <?php endif; ?>>Debtor & Vendor</option>
                                                        <option value="Debtor" <?php if(old('legalEntityType',$clientParty->
                                                            LegalEntityType) == 'Debtor'): ?> selected <?php endif; ?>>Debtor
                                                        </option>
                                                        <option value="Vendor" <?php if(old('legalEntityType',$clientParty->
                                                            LegalEntityType) == 'Vendor'): ?> selected <?php endif; ?>>Vendor
                                                        </option>
                                                        
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <fieldset class="border p-2 card shadow-lg mb-0 pt-0">
                                            <legend class="w-auto p-0 m-0">Basic Information</legend>
                                            
                                            <div class="form-group row mb-1">
                                                <label for="companyName"
                                                    class="col-sm-3 col-lg-4 col-md-4 col-form-label col-form-label-sm"><i
                                                        class="fa fa-building" aria-hidden="true"></i>Company Name<span
                                                        class="text-danger">*</span></label>
                                                <div class="col-sm-9 col-lg-8 col-md-8">
                                                    <input type="text" class="form-control form-control-sm"
                                                        value="<?php echo e(old('companyName',$clientParty->Name)); ?>"
                                                        name="companyName" id="companyName">
                                                </div>
                                            </div>
                                            
                                            <div class="form-group row mb-1">
                                                <label for="addressLine1"
                                                    class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                        class="fa fa-map-marker" aria-hidden="true"></i> AddressLine 1
                                                    <span class="text-danger">*</span></label>
                                                <div class="col-sm-9 col-lg-8">
                                                    <textarea name="addressLine1" id="addressLine1"
                                                        class="form-control form-control-sm"><?php echo e(old('addressLine1',$clientParty->Address_Line1)); ?></textarea>
                                                </div>
                                            </div>
                                            
                                            <div class="form-group row mb-1">
                                                <label for="addressLine2"
                                                    class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                        class="fa fa-map-marker" aria-hidden="true"></i> AddressLine
                                                    2</label>
                                                <div class="col-sm-9 col-lg-8">
                                                    <textarea name="addressLine2" id="addressLine2"
                                                        class="form-control form-control-sm"><?php echo e(old('addressLine2',$clientParty->Address_Line2)); ?></textarea>
                                                </div>
                                            </div>
                                            
                                            <div class="form-group row mb-1">
                                                <label for="country"
                                                    class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                        class="fa fa-globe" aria-hidden="true"></i> Country <span
                                                        class="text-danger">*</span></label>
                                                <div class="col-sm-8 col-lg-8">
                                                    
                                                    <label class="sr-only" for="country">Country</label>
                                                    <div class="form-group">
                                                        
                                                            
                                                            
                                                            <select class="form-control form-control-sm"
                                                                name="countryID" id="countryID">
                                                                <option value=""></option>
                                                                <?php $__empty_1 = true; $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                                <option
                                                                    value="<?php echo e(old('countryID',$country->CountryID)); ?>"
                                                                    id="<?php echo e($country->CountCode); ?>" <?php if(old('countryID',
                                                                    $clientParty->Address_CountryId) ==
                                                                    $country->CountryID): ?> selected <?php endif; ?> ><?php echo e($country->CountryName); ?></option>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                                                                <?php endif; ?>
                                                            </select>
                                                            
                                                        
                                                        <input type="hidden" name="countryName" id="countryName"
                                                            value="<?php echo e(old('countryName',$clientParty->Address_CountryName)); ?>">
                                                        <input type="hidden" name="countryCode" id="countryCode"
                                                            value="<?php echo e(old('countryCode',$clientParty->Address_CountryCode)); ?>">
                                                        
                                                    </div>
                                                    
                                                </div>
                                                
                                            </div>
                                            
                                            <div class="form-group row mb-1 ">
                                                <label for="province"
                                                    class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                        class="fa fa-map-pin" aria-hidden="true"></i> Province &
                                                    Zipcode</label>
                                                <div class="col-sm-4 col-lg-4">
                                                    <input type="text" class="form-control form-control-sm "
                                                        name="provinceName" id="provinceInput"
                                                        value="<?php echo e(old('provinceName', $clientParty->Address_State)); ?>"
                                                        placeholder="S / P">
                                                    <input type="hidden" name="provinceID" id="provinceID"
                                                        value="<?php echo e(old('provinceID', $clientParty->Address_StateId)); ?>">
                                                    <input type="hidden" name="provinceCode" id="provinceCode"
                                                        value="<?php echo e(old('provinceCode', $clientParty->Address_StateCode)); ?>">
                                                    
                                                </div>
                                                <div class="col-sm-4 col-lg-4">
                                                    <input type="text" class="form-control form-control-sm"
                                                        value="<?php echo e(old('zipCode',$clientParty->Address_Pin)); ?>"
                                                        name="zipCode" id="zipCode" placeholder="Zip Code">
                                                </div>
                                            </div>
                                            
                                            <div class="form-group row mb-1">
                                                <label for="cityName"
                                                    class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                        class="fas fa-city"></i> City</label>
                                                <div class="col-sm-9 col-lg-8">
                                                    <input type="text" class="form-control form-control-sm"
                                                        value="<?php echo e(old('cityName',$clientParty->Address_City)); ?>"
                                                        name="cityName" id="cityInput">
                                                    
                                                    
                                                    
                                                </div>
                                            </div>

                                        </fieldset>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <fieldset class="border p-2 card shadow-lg mb-0 pt-0">
                                            <legend class="w-auto p-0 m-0">Contact Details</legend>
                                            <div class="form-group row mb-1">
                                                <label for="person"
                                                    class="col-sm-3  col-md-4 col-lg-4 col-form-label col-form-label-sm">
                                                    <i class="fa fa-user" aria-hidden="true"></i> Person <span
                                                        style="color:red">*</span></label>
                                                
                                                <div class="col-sm-4 col-md-4 col-lg-4">
                                                    <input type="text" class="form-control form-control-sm"
                                                        value="<?php echo e(old('firstName',$clientParty->Cont_Bch_PersonName_FirstName)); ?>"
                                                        name="firstName" id="firstName" placeholder="First Name">
                                                </div>
                                                <div class="col-sm-5 col-md-4 col-lg-4">
                                                    <input type="text" class="form-control form-control-sm"
                                                        value="<?php echo e(old('lastName',$clientParty->Cont_Bch_PersonName_LastName)); ?>"
                                                        name="lastName" id="lastName" placeholder="Last Name">
                                                </div>
                                            </div>
                                            <div class="form-group row mb-1">
                                                <label for="department"
                                                    class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                        class="fa fa-sitemap" aria-hidden="true"></i> Department</label>
                                                <div class="col-sm-9 col-lg-8">
                                                    <input type="text" class="form-control form-control-sm"
                                                        value="<?php echo e(old('department',$clientParty->Cont_Bch_Dept)); ?>"
                                                        name="department" id="department" placeholder="Department">
                                                </div>
                                            </div>
                                            
                                            <div class="form-group row mb-1">
                                                <label for="phoneNo"
                                                    class="col col-sm-3  col-md-3 col-lg-4 col-form-label col-form-label-sm">
                                                    <i class="fa fa-phone" aria-hidden="true"></i> Phone No. <span
                                                        style="color:red">*</span></label>
                                                <div class="col col-sm-9 col-md-9 col-lg-8">
                                                    <input type="text" class="form-control form-control-sm number_input"
                                                        value="<?php echo e(old('phoneNo',$clientParty->Cont_Bch_PhoneNumbers)); ?>"
                                                        name="phoneNo" id="phoneNo" placeholder="Phone Number">
                                                </div>
                                            </div>
                                            
                                            <div class="form-group row mb-1">
                                                <label for="contractEmail"
                                                    class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                        class="fa fa-envelope" aria-hidden="true"></i> Email</label>
                                                <div class="col-sm-9 col-lg-8">
                                                    <input type="email" name="contractEmail" id="contractEmail"
                                                        value="<?php echo e(old('contractEmail',$clientParty->Cont_Bch_Email)); ?>"
                                                        class="form-control form-control-sm" placeholder="Enter Email">
                                                </div>
                                            </div>
                                        </fieldset>
                                        <fieldset class="border p-2 card shadow-lg mb-0">
                                            <legend class="w-auto p-0 m-0">Other information</legend>
                                            
                                            <div class="form-group row mb-1">
                                                <label for="client"
                                                    class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                        class="fa fa-user" aria-hidden="true"></i> Client <span
                                                        class="text-danger" id="clientSpan">*</span></label>
                                                <div class="col-sm-9 col-lg-8">
                                                    <div class="row">
                                                        <div class="col"><select name="clientOption" id="clientOption"
                                                                class="form-control form-control-sm" disabled>
                                                                <option value="fromMaster" <?php if(isset($clientParty->
                                                                    ClientName)): ?> selected <?php endif; ?>>Search Client
                                                                </option>
                                                                <option value="fromRequest" <?php if(isset($clientParty->
                                                                    RequestId)): ?> selected <?php endif; ?>>From Request</option>
                                                            </select></div>
                                                        <div class="col">
                                                            <?php if(isset($clientParty->RequestId)): ?>
                                                            <input type="text" name="clientOptionVal"
                                                                id="clientOptionVal"
                                                                value="<?php echo e(old('clientOptionVal', $clientParty->RequestName)); ?>"
                                                                class="form-control form-control-sm"
                                                                <?php if(isset($request->RequestId)): ?> <?php else: ?> disabled <?php endif; ?>>
                                                            <input type="hidden" name="requestID"
                                                                value="<?php echo e(old('requestID', $clientParty->RequestId)); ?>"
                                                                id="requestID">
                                                            <input type="hidden" name="requestCode"
                                                                value="<?php echo e(old('requestCode', $clientParty->RequestCode)); ?>"
                                                                id="requestCode">
                                                            <?php else: ?>
                                                            <input type="text" name="client" id="client"
                                                                value="<?php echo e(old('client', $clientParty->ClientName)); ?>"
                                                                class="form-control form-control-sm"
                                                                <?php if(isset($request->ClientName)): ?> <?php else: ?> disabled <?php endif; ?> >
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row mb-1">
                                                <label for="specialInstructions"
                                                    class="col-sm-3 col-lg-4 col-form-label col-form-label-sm"><i
                                                        class="fas fa-comments"></i> Special
                                                    Instructions</label>
                                                <div class="col-sm-9 col-lg-8">
                                                    <textarea class="form-control form-control-sm"
                                                        name="specialInstruction" id="specialInstruction"
                                                        placeholder="Special Instructions"><?php echo e(old('specialInstruction', $clientParty->SpecialInstructions)); ?></textarea>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                                <button class="btn btn-primary m-2 btn-sm float-right" name="next" type="button"
                                    onclick="stepper.next()">Next <i class="fa fa-step-forward"
                                        aria-hidden="true"></i></button>
                                <button type="button" name="updateBtn" id="save-step1"
                                    class="btn btn-success btn-sm float-right m-2" data-toggle="tooltip"
                                    data-placement="top" title="" data-bs-original-title="Update"><i class="fa fa-edit"
                                        aria-hidden="true"></i> Update for Later</button>
                            </div>
                            <div id="office-details" class="content active dstepper-block" role="tabpanel"
                                aria-labelledby="office-details-trigger">
                                <div class="row">
                                    <div class="col-md-4 col-lg-4 col-sm-12">
                                        <fieldset class="border p-2 card shadow-sm mb-0 pt-0 pb-0">
                                            <legend class="w-auto p-0 m-0">Office Mapping</legend>
                                            <div class="form-group row m-0">
                                                <label for="officeCountry"
                                                    class="col-sm-6 col-lg-4 col-md-4 col-form-label col-form-label-sm">Office
                                                    <span class="text-danger">*</span></label>
                                                <div class="col-sm-6 col-lg-8 col-md-8">
                                                    <input type="hidden" name="officeCountryDisp"
                                                        code="<?php echo e($clientParty->OfficeCode); ?>" id="officeCountryDisp"
                                                        value="<?php echo e($clientParty->OfficeCode); ?>">
                                                    <select class="form-control form-control-sm" name="officeCountry"
                                                        id="officeCountry" required <?php if($clientParty->Status ==
                                                        "Submitted"): ?> disabled <?php endif; ?>>
                                                        <option value="" disabled selected></option>
                                                        <?php $__empty_1 = true; $__currentLoopData = $offices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $office): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                        <option code="<?php echo e($office->Code); ?>" value="<?php echo e($office->Name); ?>"
                                                            id="<?php echo e($office->ID); ?>" <?php if($office->ID ==
                                                            (count($clientParty->getSCMOfficeID($clientParty->OfficeID
                                                            ?? Auth::user()->office->SCMOfficeID)) < 1 ? ($clientParty->
                                                                OfficeID ?? Auth::user()->office->SCMOfficeID) :
                                                                $clientParty->getSCMOfficeID($clientParty->OfficeID ??
                                                                Auth::user()->office->SCMOfficeID)[0]->SCMOfficeID)): ?>
                                                                selected <?php endif; ?> ><?php echo e($office->Name); ?></option>
                                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                        <?php endif; ?>
                                                    </select>
                                                    <?php if(!Auth::user()->can('create-client-for-any-office') ||
                                                    ($clientParty->Status == 'Submitted')): ?>
                                                    <input type="hidden" name="officeCountry"
                                                        code="<?php echo e($clientParty->OfficeCode); ?>"
                                                        value="<?php echo e(old('officeCountry', $clientParty->Office)); ?>">
                                                    <input type="hidden" name="officeCode"
                                                        value='<?php echo e($clientParty->OfficeCode); ?>'>
                                                    <?php endif; ?>
                                                    <input type="hidden" name="officeCountryID" id="officeCountryID"
                                                        value="<?php echo e(old('officeCountryID', $clientParty->OfficeID)); ?>">
                                                </div>
                                                
                                            </div>
                                        </fieldset>
                                        <fieldset class="border p-2 card shadow-lg mb-0 pt-0 pb-0">
                                            <legend class="w-auto pb-0 mb-0">Prefered Ports</legend>
                                            <div class="row pt-0 mt-0 justify-content-center">
                                                <div class="col-md-8 col-sm-8 col-lg-8 ">
                                                    <div class="form-check">
                                                        <label class="form-check-label" for="includeCountryCheckBox">
                                                            Include Country
                                                        </label>
                                                        <input class="form-check-input ml-3" type="checkbox"
                                                            name="includeCountryCheckBox" id="includeCountryCheckBox">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 col-sm-12 col-lg-6">
                                                    
                                                    <div class="form-group  pt-0">
                                                        <div class="">
                                                            <label for="portOfLoadingAir"
                                                                class="col-form-label col-form-label-sm">Port Of
                                                                Loading(Air) </label>
                                                            <input type="text" class="form-control form-control-sm"
                                                                value="<?php echo e(old('portOfLoadingAir',$clientParty->AirUsualPortOfLoadingName)); ?>"
                                                                name="portOfLoadingAir" id="portOfLoadingAir">
                                                            <input type="hidden" name="portOfLoadingAirID"
                                                                id="portOfLoadingAirID"
                                                                value="<?php echo e(old('portOfLoadingAirID',$clientParty->AirUsualPortOfLoading)); ?>">
                                                            
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group ">
                                                        <div class="">
                                                            <label for="portOfDischargeAir"
                                                                class="col-form-label col-form-label-sm">Port Of
                                                                Discharge(Air)</label>
                                                            <input type="text" class="form-control form-control-sm"
                                                                value="<?php echo e(old('portOfDischargeAir',$clientParty->AirUsualPortofDischargeName)); ?>"
                                                                name="portOfDischargeAir" id="portOfDischargeAir">
                                                            <input type="hidden" class="form-control form-control-sm"
                                                                value="<?php echo e(old('portOfDischargeAirID', $clientParty->AirUsualPortofDischarge)); ?>"
                                                                name="portOfDischargeAirID" id="portOfDischargeAirID">
                                                            
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6 col-sm-12 col-lg-6">
                                                    
                                                    <div class="form-group row mb-1">
                                                        <div class="col-md-12">
                                                            <label for="portOfLoadingSea"
                                                                class="col-form-label col-form-label-sm">Port Of
                                                                Loading(Sea) </label>
                                                            <input type="text" class="form-control form-control-sm"
                                                                value="<?php echo e(old('portOfLoadingSea',$clientParty->SeaUsualPortOfLoadingName)); ?>"
                                                                name="portOfLoadingSea" id="portOfLoadingSea">
                                                            <input type="hidden" class="form-control form-control-sm"
                                                                value="<?php echo e(old('portOfLoadingSeaID',$clientParty->SeaUsualPortOfLoading)); ?>"
                                                                name="portOfLoadingSeaID" id="portOfLoadingSeaID">
                                                            
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group row mb-1">
                                                        <div class="col-md-12">
                                                            <label for="portOfDischargeSea"
                                                                class="col-form-label col-form-label-sm">Port Of
                                                                Discharge(Sea)</label>
                                                            <input type="text" class="form-control form-control-sm"
                                                                value="<?php echo e(old('portOfDischargeSea',$clientParty->SeaUsualPortofDischargeName)); ?>"
                                                                name="portOfDischargeSea" id="portOfDischargeSea">
                                                            <input type="hidden" class="form-control form-control-sm"
                                                                value="<?php echo e(old('portOfDischargeSeaID',$clientParty->SeaUsualPortofDischarge)); ?>"
                                                                name="portOfDischargeSeaID" id="portOfDischargeSeaID">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                       <fieldset class="border p-2 card shadow-lg mb-0 pt-0 pb-0">
                                            <legend>File Upload</legend>
                                            <?php if(count($clientParty->attachments) < 3): ?>
                                                <label for="attachmentFile">Attachment <i class="fa fa-paperclip"></i>
                                                    <button type="button" class="btn btn-sm btn-outline-warning pull-right w-20 addAttachment" id="addFile">
                                                        <i class="fa fa-plus"></i> File
                                                    </button>
                                                </label>
                                                <div class="input-group hdtuto control-group lst increment">
                                                    <input type="file" name="attachmentNames[]" class="myfrm form-control mr-1 form-control-sm">
                                                    <input type="text" name="attachmentDescription[]" class="myfrm form-control form-control-sm"
                                                        placeholder="Description">
                                                </div>
                                                <div id="attachClone"></div>
                                                <div class="clone d-none">
                                                    <div class="hdtuto control-group lst input-group mt-1" style="">
                                                        <input type="file" name="attachmentNames[]" class="myfrm form-control mr-1 form-control-sm">
                                                        <input type="text" name="attachmentDescription[]" class="myfrm form-control form-control-sm"
                                                            placeholder="Description">
                                                        <div class="input-group-btn">
                                                            <button class="btn btn-danger btn-sm" type="button" style="">
                                                                <i class="fldemo fa fa-minus"></i> </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                            <?php if(count($clientParty->attachments) > 0): ?>
                                                <ul>
                                                    <?php $__currentLoopData = $clientParty->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <li><?php echo e($attachment->FileName); ?> | <?php echo e($attachment->Title); ?> <a href="#" class="deleteFile" id="<?php echo e($attachment->ID); ?>"><i class="fa fa-times-circle text-danger"></i></a></li>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </ul>
                                            <?php endif; ?>
                                        </fieldset>
                                    </div>
                                    <div class="col-md-8 col-lg-8 col-sm-12">
                                        <fieldset class="border p-2 card shadow-lg mb-0 pt-0">
                                            <legend class="w-auto p-0 m-0">Client</legend>
                                            <div class="row">
                                                
                                                <div class="col-md-6 col-sm-12 col-lg-6">
                                                    
                                                    <div class="form-group row mb-1">
                                                        <label for="salesPIC"
                                                            class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Sales
                                                            PIC <span class="text-danger">*</span></label>
                                                        <div class="col-sm-9 col-lg-8">
                                                            <input type="text" class="form-control form-control-sm"
                                                                value="<?php echo e(old('salesPIC', $clientParty->SalesMgrOfficeFunMapName)); ?>"
                                                                name="salesPIC" id="salesPIC" placeholder="">
                                                            <input type="hidden" name="salesPICID" id="salesPICID"
                                                                value="<?php echo e(old('salesPICID', $clientParty->SalesMgrOfficeFunMapId)); ?>">
                                                            
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group row mb-1">
                                                        <label for="incoTermID"
                                                            class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Inco
                                                            Terms</label>
                                                        <div class="col-sm-9 col-lg-8">
                                                            <select class="form-control form-control-sm"
                                                                name="incoTermID" id="incoTermID">
                                                                <option value="" disabled selected></option>
                                                                <?php $__empty_1 = true; $__currentLoopData = $incoterms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $incoterm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                                <option
                                                                    value="<?php echo e(old('incoTermID', $incoterm->IncoTermID)); ?>"
                                                                    id="<?php echo e(old('incoTermCode', $incoterm->IncoTermCode)); ?>"
                                                                    <?php if(old('incoTermID', $clientParty->IncoTermID) ==
                                                                    $incoterm->IncoTermID): ?> selected <?php endif; ?>><?php echo e($incoterm->Name); ?></option>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                                                                <?php endif; ?>
                                                            </select>
                                                            <input type="hidden" name="incoTermName" id="incoTermName"
                                                                value="<?php echo e(old('incoTermName',$clientParty->IncoTermName)); ?>">
                                                            <input type="hidden" name="incoTermCode" id="incoTermCode"
                                                                value="<?php echo e(old('incoTermCode',$clientParty->IncoTermCode)); ?>">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group row mb-1">
                                                        <label for="notifyParty1"
                                                            class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Notify
                                                            Party 1</label>
                                                        <div class="col-sm-9 col-lg-8">
                                                            <input type="text" class="form-control form-control-sm"
                                                                value="<?php echo e(old('notifyParty1', $clientParty->DefaultNotifyPartyName)); ?>"
                                                                name="notifyParty1" id="notifyParty1" placeholder="">
                                                            <input type="hidden" name="notifyParty1ID"
                                                                value="<?php echo e(old('notifyParty1ID', $clientParty->DefaultNotifyPartyId)); ?>"
                                                                id="notifyParty1ID">
                                                            <input type="hidden" name="notifyParty1Code"
                                                                value="<?php echo e(old('notifyParty1Code', $clientParty->DefaultNotifyPartyCode)); ?>"
                                                                id="notifyParty1Code">
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- /.end col 1 office details -->
                                                
                                                <div class="col-md-6 col-sm-12 col-lg-6 pt-0">
                                                    
                                                    <div class="form-group row mb-1">
                                                        <label for="accountPIC"
                                                            class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Account
                                                            PIC</label>
                                                        <div class="col-sm-9 col-lg-8">
                                                            <input type="text" class="form-control form-control-sm"
                                                                value="<?php echo e(old('accountPIC', $clientParty->AccountMgrOfficeFunMapName)); ?>"
                                                                name="accountPIC" id="accountPIC" placeholder="">
                                                            <input type="hidden" name="accountPICID" id="accountPICID"
                                                                value="<?php echo e(old('accountPICID',$clientParty->AccountMgrOfficeFunMapId)); ?>">
                                                            
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group row mb-1">
                                                        <label for="paymentTermsID"
                                                            class="col-sm-3 col-lg-4 col-form-label col-form-label-sm pt-0 mt-0">Pay
                                                            Terms</label>
                                                        <div class="col-sm-9 col-lg-8 ">
                                                            <select class="form-control form-control-sm"
                                                                name="paymentTermsID" id="paymentTermsID">
                                                                <option value="" disabled selected></option>
                                                                <?php $__empty_1 = true; $__currentLoopData = $payTerms; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payTerm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                                <option
                                                                    value="<?php echo e(old('paymentTermsID', $payTerm->IncoTermID)); ?>"
                                                                    id="<?php echo e($payTerm->IncoTermCode); ?>"
                                                                    <?php if(old('paymentTermsID', $payTerm->IncoTermID)
                                                                    ==$clientParty->PaymentTermsID): ?> selected <?php endif; ?>><?php echo e($payTerm->Name); ?></option>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                                                                <?php endif; ?>
                                                            </select>
                                                            <input type="hidden" name="paymentTermsName"
                                                                value="<?php echo e(old('paymentTermsName', $clientParty->PaymentTermsName)); ?>"
                                                                id="paymentTermsName">
                                                            <input type="hidden" name="paymentTermsCode"
                                                                id="paymentTermsCode"
                                                                value="<?php echo e(old('paymentTermsCode', $clientParty->PaymentTermsCode)); ?>">
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group row mb-1">
                                                        <label for="notifyParty2"
                                                            class="col-sm-3 col-lg-4 col-form-label col-form-label-sm">Notify
                                                            Party 2</label>
                                                        <div class="col-sm-9 col-lg-8">
                                                            <input type="text" class="form-control form-control-sm"
                                                                value="<?php echo e(old('notifyParty2', $clientParty->DefaultNotifyParty1Name)); ?>"
                                                                name="notifyParty2" id="notifyParty2" placeholder="">
                                                            <input type="hidden" name="notifyParty2ID"
                                                                value="<?php echo e(old('notifyParty2ID', $clientParty->DefaultNotifyParty1Id)); ?>"
                                                                id="notifyParty2ID">
                                                            <input type="hidden" name="notifyParty2Code"
                                                                value="<?php echo e(old('notifyParty2Code', $clientParty->DefaultNotifyParty1Code)); ?>"
                                                                id="notifyParty2Code">
                                                            
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- /.end col 2 office details -->
                                            </div>
                                            <!-- row 1 office details -->
                                            <div class="">
                                                
                                                <div class="form-group row mb-1">
                                                    <label for="forwarderName"
                                                        class="col-sm-3 col-md-4 col-lg-2 col-form-label col-form-label-sm">Forwarder</label>
                                                    <div class="col-sm-9 col-lg-10 col-md-8">
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="forwarderName" id="forwarderName"
                                                            value="<?php echo e(old('forwarderName', $clientParty->DefaultFowarderName)); ?>">
                                                        <input type="hidden" class="form-control form-control-sm"
                                                            name="forwarderID" id="forwarderID"
                                                            value="<?php echo e(old('forwarderID', $clientParty->DefaultFowarderId)); ?>">
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                        <fieldset class="border p-2 card shadow-lg mb-0 pt-0 ">
                                            <legend class="w-auto p-0 m-0">Debtor/Vendor</legend>
                                            <div class="row">
                                                <div class="col-md-6 col-sm-12 col-lg-6">
                                                    
                                                    <div class="form-group row mb-1">
                                                        <div class="col-md-12">
                                                            <label for="debtorType"
                                                                class="col-form-label col-form-label-sm">Debtor
                                                                Type</label>
                                                            <select class="form-control form-control-sm"
                                                                name="debtorType" id="debtorType">
                                                                <option value="" disabled selected></option>
                                                                
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    
                                                    <div class=" row mb-0 pb-0">
                                                        <div class="form-group col-md-6 mb-0 pb-0">
                                                            <label for="billingCurrency"
                                                                class="col-form-label col-form-label-sm">Billing
                                                                Currency</label>
                                                            <input type="hidden" class="form-control form-control-sm"
                                                                value="<?php echo e(old('billingCurrencyCode', $clientParty->BillingCurrencyCode)); ?>"
                                                                name="billingCurrencyCode" id="billingCurrencyCode">
                                                            
                                                            <select class="form-control form-control-sm"
                                                                name="billingCurrency" id="billingCurrency">
                                                                <option value="" disabled selected></option>
                                                                <?php $__empty_1 = true; $__currentLoopData = $currencyList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                                <option value="<?php echo e($item->CurrencyID); ?>"
                                                                    id="<?php echo e($item->CurrencyCode); ?>"
                                                                    <?php if(old('billingCurrency', $clientParty->
                                                                    BillingCurrencyId) == $item->CurrencyID): ?> selected
                                                                    <?php endif; ?>><?php echo e($item->CurrencyCode); ?></option>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                                <option value="" disabled selected></option>
                                                                <?php endif; ?>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="form-group col-md-6 mb-0 pb-0">
                                                            <label for="creditDays"
                                                                class="col-form-label col-form-label-sm">Credit
                                                                Days</label>
                                                            <input type="text"
                                                                class="form-control form-control-sm number_input"
                                                                value="<?php echo e(old('creditDays', $clientParty->CreditDays)); ?>"
                                                                name="creditDays" id="creditDays">
                                                        </div>
                                                    </div>

                                                </div>
                                                <div class="col-md-6 col-sm-12 col-lg-6">
                                                    
                                                    <div class="form-group row mb-1">
                                                        <div class="col-md-12">
                                                            <label for="vendorType"
                                                                class="col-form-label col-form-label-sm">Vendor
                                                                Type</label>
                                                            <select class="form-control form-control-sm"
                                                                name="vendorType" id="vendorType">
                                                                <option value="" disabled selected></option>
                                                                
                                                            </select>
                                                        </div>
                                                    </div>

                                                    
                                                    <div class="form-group">
                                                        <div class="row">
                                                            <div class="col-sm-6 col-md-6 col-lg-6 ">
                                                                <label for="minCreditLimit"
                                                                    class="col-form-label col-form-label-sm">Credit
                                                                    Limit</label>
                                                                <input type="text"
                                                                    class="form-control form-control-sm number_input"
                                                                    value="<?php echo e(old('maxCreditLimit',$clientParty->CreditLimit)); ?>"
                                                                    name="maxCreditLimit" id="maxCreditLimit"
                                                                    placeholder="Credit Limit">
                                                            </div>
                                                            <div class="col-sm-6 col-md-6 col-lg-6 ">
                                                                <label for="baseCurrency"
                                                                    class="col-form-label col-form-label-sm">Base
                                                                    Currency</label>
                                                                <input type="text" class="form-control form-control-sm"
                                                                    name="baseCurrency" id="baseCurrency"
                                                                    value="<?php echo e(old('baseCurrencyCode', $clientParty->BaseCurrencyCode)); ?>"
                                                                    disabled>
                                                                <input type="hidden"
                                                                    class="form-control form-control-sm"
                                                                    name="baseCurrencyCode" id="baseCurrencyCode"
                                                                    value="<?php echo e(old('baseCurrencyCode', $clientParty->BaseCurrencyCode)); ?>">
                                                                <input type="hidden"
                                                                    class="form-control form-control-sm"
                                                                    name="baseCurrencyName" id="baseCurrencyName"
                                                                    value="<?php echo e(old('baseCurrencyName', $clientParty->BaseCurrencyName)); ?>">
                                                                <input type="hidden"
                                                                    class="form-control form-control-sm"
                                                                    name="baseCurrencyID" id="baseCurrencyID"
                                                                    value="<?php echo e(old('baseCurrencyID', $clientParty->BaseCurrencyId)); ?>">

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="form-group row mb-0 mt-0 p-0 ">
                                                <div class="col col-sm-6 col-lg-6 col-md-6 m-0">
                                                    <div class="row">
                                                        <label for="licence"
                                                            class="col-md-3 col-form-label col-form-label-sm pr-0">Licence
                                                            Type.</label>
                                                        <div class="col-md-9">
                                                            <select class="form-control form-control-sm"
                                                                name="licenceTypeID" id="licenceTypeID">
                                                                <option value="" disabled selected></option>
                                                                <?php $__empty_1 = true; $__currentLoopData = $licenceTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                                <option value="<?php echo e($type->LicenseID); ?>"
                                                                    id="<?php echo e($type->LicenseCode); ?>"
                                                                    <?php if(old('licenceTypeID',$type->LicenseID ) ==
                                                                    $clientParty->LicenceTypeID): ?> selected <?php endif; ?>><?php echo e($type->LicenseName); ?></option>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                                <?php endif; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col col-sm-6 col-lg-6 col-md-6">
                                                    <input type="hidden" class="form-control form-control-sm"
                                                        value="<?php echo e(old('licenceTypeCode', $clientParty->LicenceTypeCode)); ?>"
                                                        name="licenceTypeCode" id="licenceTypeCode"><input type="text"
                                                        class="form-control form-control-sm"
                                                        value="<?php echo e(old('licenceTypeValue', $clientParty->LicenceTypeValue)); ?>"
                                                        name="licenceTypeValue" id="licenceTypeValue">
                                                </div>
                                            </div>
                                        </fieldset>
                                        <fieldset class="border p-2 card shadow-lg mb-0 pt-0">
                                            <legend class="w-auto p-0 m-0"> Additional Information</legend>
                                            <div class="row">
                                                <div class="col-md-6 col-sm-12 col-lg-6">
                                                    
                                                    <div class="form-group row mb-1">
                                                        <div class="col-md-12">
                                                            <label for="tarrif"
                                                                class="col-form-label col-form-label-sm">Tarrif</label>
                                                            <select class="form-control form-control-sm" name="tarrif"
                                                                id="tarrif" required>
                                                                <option value="" selected disabled></option>
                                                                <?php $__empty_1 = true; $__currentLoopData = $tarrifs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tarrif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                                <option value="<?php echo e($tarrif->TARIFF); ?>" <?php if(old('tarrif',
                                                                    $clientParty->TariffValue) == $tarrif->TARIFF): ?>
                                                                    selected <?php endif; ?>><?php echo e($tarrif->TARIFF); ?></option>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                                <?php endif; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group row mb-1">
                                                        <div class="col-md-12">
                                                            <label for="phoneNo"
                                                                class="col-form-label col-form-label-sm"> FSC Type &
                                                                Rate</label>
                                                            <div class="row">
                                                                <div class="col-sm-6 col-lg-6">
                                                                    <select class="form-control form-control-sm"
                                                                        name="fscType" id="fscType" required>
                                                                        <option value="" selected disabled>Select Type
                                                                        </option>
                                                                        <option value="Fixed"
                                                                            <?php if(old('fscType',$clientParty->FSCType) ==
                                                                            'Fixed'): ?> selected <?php endif; ?>>Fixed</option>
                                                                        <option value="Variable" <?php if(old('fscType',
                                                                            $clientParty->FSCType) == 'FixedVariable'): ?>
                                                                            selected <?php endif; ?>>Variable</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-sm-6 col-lg-6">
                                                                    <input type="text"
                                                                        class="form-control form-control-sm"
                                                                        value="<?php echo e(old('fscRate',$clientParty->FSCRate)); ?>"
                                                                        name="fscRate" id="fscRate" placeholder="Rate"
                                                                        required>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-sm-12 col-lg-6">
                                                    
                                                    <div class="form-group row mb-1">
                                                        <div class="col-md-12">
                                                            <label for="billingType"
                                                                class="col-form-label col-form-label-sm">Billing
                                                                Type</label>
                                                            <select class="form-control form-control-sm"
                                                                name="billingType" id="billingType" required>
                                                                <option value="" selected>Select Billing Type </option>
                                                                <option value="Periodic"
                                                                    <?php if(old('billingType',$clientParty->BillingType) ==
                                                                    'Periodic'): ?> selected <?php endif; ?>>Periodic</option>
                                                                <option value="Shipment"
                                                                    <?php if(old('billingType',$clientParty->BillingType) ==
                                                                    'Shipment'): ?> selected <?php endif; ?>>Shipment</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group row mb-1">
                                                        <div class="col-md-12">
                                                            <label for="discount"
                                                                class="col-form-label col-form-label-sm">Discount in
                                                                %</label>
                                                            <input type="text" class="form-control form-control-sm"
                                                                value="<?php echo e(old('discount', number_format($clientParty->Discount,2))); ?>"
                                                                name="discount" id="discount" placeholder="Discount"
                                                                required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                                <button class="btn btn-primary m-2 btn-sm" type="button" name="previous"
                                    onclick="stepper.previous()"><i class="fa fa-step-backward" aria-hidden="true"></i>
                                    Previous</button>
                                <?php if(Auth::user()->can('create-client-party')): ?>
                                <button class="btn btn-primary m-2 btn-sm float-right" type="button" name="next"
                                    onclick="stepper.next()">Next <i class="fa fa-step-forward"
                                        aria-hidden="true"></i></button>
                                <button type="button" name="updateBtn" id="save-step2"
                                    class="btn btn-success btn-sm float-right m-2" data-toggle="tooltip"
                                    data-placement="top" title="" data-bs-original-title="Update"><i class="fa fa-edit"
                                        aria-hidden="true"></i> Update for Later</button>
                                <?php else: ?>
                                <button type="submit" name="submitBtn" value="true"
                                    class="btn btn-outline-success btn-sm float-right m-2" data-toggle="tooltip"
                                    data-placement="top" title="Will be processed by admin"><i class="fa fa-thumbs-up"
                                        aria-hidden="true"></i> Submit</button>
                                <button type="submit" name="updateBtn" id="updateBtn"
                                    class="btn btn-success btn-sm float-right m-2" data-toggle="tooltip"
                                    data-placement="top" title="Update"><i class="fa fa-edit" aria-hidden="true"></i>
                                    Update for Later</button>
                                <?php endif; ?>
                            </div>
                            <?php if(Auth::user()->can('create-client-party')): ?>
                            <div id="admin-details" class="content" role="tabpanel"
                                aria-labelledby="admin-details-trigger">
                                <div class="row justify-content-center">
                                    <div class="com-md-6 col-xl-6 col-sm-12 col-lg-6">
                                        <fieldset class="border p-2 card shadow-lg mb-0 pt-0">
                                            <legend class="w-auto p-0 m-0"></legend>
                                            <table class="table table-sm table-borderless">
                                                <thead>
                                                    <th>Entity</th>
                                                    <th>Create</th>
                                                    <th>Select</th>
                                                    <th>Code</th>
                                                </thead>
                                                <tr>
                                                    <td>Client</td>
                                                    <td><input type="checkbox" name="clientCheck" id="clientCheck"
                                                            <?php if($clientParty->ClientName == ''): ?> checked <?php endif; ?>></td>
                                                    <td>
                                                        <input type="text" name="clientInput" id="clientInput"
                                                            value="<?php echo e(old('clientInput',$clientParty->ClientName)); ?>"
                                                            class="form-control form-control-sm" required>
                                                        <input type="hidden" name="clientID" id="clientID"
                                                            value="<?php echo e(old('clientID',$clientParty->ClientID)); ?>">
                                                        <input type="hidden" name="clientCode" id="clientCode"
                                                            value="<?php echo e(old('clientCode',$clientParty->ClientCode)); ?>">
                                                        
                                                    </td>
                                                    <td id="client_Input" onclick='copyText(this)' data-toggle="tooltip"
                                                        title="Click to copy"><?php echo e($clientParty->ClientCode ?? ''); ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Shipper</td>
                                                    <td><input type="checkbox" name="shipperCheck" id="shipperCheck"
                                                            <?php if($clientParty->ShipperConsigneeName == ''): ?> checked
                                                        <?php endif; ?>></td>
                                                    <td>
                                                        <input type="text" name="shipperInput" id="shipperInput"
                                                            value="<?php echo e(old('shipperInput',$clientParty->ShipperConsigneeName)); ?>"
                                                            class="form-control form-control-sm" required>
                                                        <input type="hidden" name="shipperID" id="shipperID"
                                                            value="<?php echo e(old('shipperID',$clientParty->ShipperConsigneeID)); ?>">
                                                        <input type="hidden" name="shipperCode" id="shipperCode"
                                                            value="<?php echo e(old('shipperID',$clientParty->ShipperConsigneeCode)); ?>">
                                                        
                                                    </td>
                                                    <td id="shipper_Input" onclick='copyText(this)'
                                                        data-toggle="tooltip" title="Click to copy"><?php echo e($clientParty->ShipperConsigneeCode ?? ''); ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Consignee</td>
                                                    <td><input type="checkbox" name="consigneeCheck" id="consigneeCheck"
                                                            <?php if($clientParty->ShipperConsigneeName == ''): ?> checked
                                                        <?php endif; ?>></td>
                                                    <td>
                                                        <input type="text" name="consigneeInput" id="consigneeInput"
                                                            value="<?php echo e(old('consigneeInput',$clientParty->ShipperConsigneeName)); ?>"
                                                            class="form-control form-control-sm" required>
                                                        <input type="hidden" name="consigneeID" id="consigneeID"
                                                            value="<?php echo e(old('consigneeID',$clientParty->ShipperConsigneeID)); ?>">
                                                        <input type="hidden" name="consigneeCode" id="consigneeCode"
                                                            value="<?php echo e(old('consigneeCode',$clientParty->ShipperConsigneeCode)); ?>">

                                                    </td>
                                                    <td id="consignee_Input" onclick='copyText(this)'
                                                        data-toggle="tooltip" title="Click to copy"><?php echo e($clientParty->ShipperConsigneeCode ?? ''); ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Other</td>
                                                    <td><input type="checkbox" name="otherCheck" id="otherCheck"
                                                            <?php if($clientParty->OtherPartyName == ''): ?> checked <?php endif; ?>></td>
                                                    <td>
                                                        <input type="text" name="otherInput" id="otherInput"
                                                            value="<?php echo e(old('otherID',$clientParty->OtherPartyName)); ?>"
                                                            class="form-control form-control-sm" required>
                                                        <input type="hidden" name="otherID" id="otherID"
                                                            value="<?php echo e(old('otherID',$clientParty->OtherPartyID)); ?>">
                                                        <input type="hidden" name="otherCode" id="otherCode"
                                                            value="<?php echo e(old('otherCode',$clientParty->OtherPartyCode)); ?>">

                                                    </td>

                                                    <td id="other_Input" onclick='copyText(this)' data-toggle="tooltip"
                                                        title="Click to copy"><?php echo e($clientParty->OtherPartyCode ?? ''); ?>

                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Debtor</td>
                                                    <td><input type="checkbox" name="debtorCheck" id="debtorCheck"
                                                            <?php if($clientParty->RequestforDebtor): ?> checked <?php endif; ?>></td>
                                                    <td>
                                                        <input type="text" name="debtor" id="debtor"
                                                            value="<?php echo e(old('debtor', $clientParty->Debtor)); ?>"
                                                            class="form-control form-control-sm" required>
                                                        <input type="hidden" name="debtorID"
                                                            value="<?php echo e(old('debtorID',$clientParty->DebtorID)); ?>"
                                                            id="debtorID">
                                                        <input type="hidden" name="debtorCode"
                                                            value="<?php echo e(old('debtorCode', $clientParty->DebtorCode)); ?>"
                                                            id="debtorCode">

                                                    </td>
                                                    <td id="debtor_code" onclick='copyText(this)' data-toggle="tooltip"
                                                        title="Click to copy"><?php echo e($clientParty->DebtorCode ?? ''); ?></td>
                                                </tr>
                                                <tr>
                                                    <td>Vendor</td>
                                                    <td><input type="checkbox" name="vendorCheck" id="vendorCheck"
                                                            <?php if($clientParty->RequestforVendor): ?> checked <?php endif; ?>></td>
                                                    <td>
                                                        <input type="text" name="vendor" id="vendor"
                                                            value="<?php echo e(old('vendor',$clientParty->Vendor)); ?>"
                                                            class="form-control form-control-sm" required>
                                                        <input type="hidden" name="vendorID"
                                                            value="<?php echo e(old('vendorID',$clientParty->VendorID)); ?>"
                                                            id="vendorID">
                                                        <input type="hidden" name="vendorCode"
                                                            value="<?php echo e(old('vendorCode',$clientParty->VendorCode)); ?>"
                                                            id="vendorCode">

                                                    </td>
                                                    <td id="vendor_code" onclick="copyText(this)" data-toggle="tooltip"
                                                        title="Click to Copy"><?php echo e($clientParty->VendorCode ?? ''); ?></td>
                                                </tr>
                                            </table>
                                        </fieldset>
                                        <button class="btn btn-primary m-2 btn-sm" type="button" name="previous"
                                            onclick="stepper.previous()"><i class="fa fa-step-backward"
                                                aria-hidden="true"></i> Previous</button>
                                        <?php if($approve): ?>
                                        <button type="button" name="approveBtn" id="approveBtn"
                                            class="btn btn-outline-success float-right m-2 btn-sm" data-toggle="tooltip"
                                            data-placement="top" title=""
                                            data-bs-original-title="Approve For Creation"><i class="fa fa-check"
                                                aria-hidden="true"></i> Approve</button>
                                        <button type="button" name="rejectBtn" id="rejectBtn"
                                            data-toggle="modal" data-target="#cancelModal"
                                            class="btn btn-danger float-right m-2 btn-sm" data-toggle="tooltip"
                                            data-placement="top" title="Reject For Amendment" data-bs-original-title="Update">
                                            <i class="fa fa-edit" aria-hidden="true"></i> Reject </button>
                                        <?php else: ?>
                                        <button type="submit" name="createBtn" value="true"
                                            class="btn btn-outline-success float-right m-2 btn-sm" data-toggle="tooltip"
                                            data-placement="top" title=""
                                            data-bs-original-title="Update and Create On SCM"><i class="fa fa-cogs"
                                                aria-hidden="true"></i> Create</button>
                                        <button type="submit" name="submitBtn" value="true"
                                            class="btn btn-success float-right m-2 btn-sm" data-toggle="tooltip"
                                            data-placement="top" title="" data-bs-original-title="Update">
                                            <i class="fa fa-edit" aria-hidden="true"></i> Update for Later</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                        </form>
                    </div>
                </div>
            </div>
            <!-- /.card-body -->
            <div class="card-footer"></div>
            <div id="loader"></div>
        </div>
        <!-- /.card -->
    </div>
</div>
<div class="row">
    <div class="col-md-12 text-center">
        Request ID: <span class="font-weight-bold" onclick='copyText(this)' data-toggle="tooltip"
            title="Click to copy"><?php echo e($clientParty->Code); ?></span> </b> Requested By: <b><?php echo e($clientParty->CreatedBy); ?></b> Requested On: <b><?php echo e($clientParty->CreatedOn); ?></b> <br>
        Status: <span
            class="badge badge-<?php echo e($clientParty->paintClientPartyStatus($clientParty->Status)); ?>"><?php echo e($clientParty->Status); ?></span>
        <?php if(($clientParty->Status == 'Rejected') || ($clientParty->Status == 'Review')): ?>Rejected By: <b><?php echo e($clientParty->RejectedBy); ?></b> RejectedOn: <b><?php echo e(date_format(date_create($clientParty->RejectedOn),'Y-m-d
            H:i:s')); ?></b> <br>Reason: <b class="text-sm"><?php echo e($clientParty->RejectedReason); ?></b><?php endif; ?>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Reject Entity Options</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="" id="cancelForm" >
                    <div class="form-group">
                        <label for="level" class="control-label">Level</label>
                        <select name="level" id="level" class="form-control">
                            <option value="" selected disabled>Select an option</option>
                            <option value="Review">Reject for Review</option>
                            <option value="Rejected">Reject Permanently</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="reason" class="control-label">Reason</label>
                        <textarea name="reason" id="reason" class="form-control"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="close-modal">Close</button>
                <button type="button" class="btn btn-primary" id="submit-entity">Save
                    changes</button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('css'); ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bs-stepper/dist/css/bs-stepper.min.css">
<style>
    fieldset.scheduler-border {
        border: solid 1px #DDD !important;
        padding: 0 10px 10px 10px;
        border-bottom: none;
    }

    legend.scheduler-border {
        width: auto !important;
        border: none;
        font-size: 14px;
    }

    legend {
        font-size: 16px;
        /* color: #060ac4; */
        /* color: #0174f3; */
        color: #E91809;
        /* padding: 0 10px 10px 10px; */
        /**/
        font-weight: bold;
    }

    label {
        font-weight: bold;
        /* font-size: 18px; */
    }

    /* Preloader */
    .preloader {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 999999999;
        width: 100%;
        height: 100%;
        background-color: #fff;
        overflow: hidden;
    }

    .preloader-inner {
        position: absolute;
        top: 50%;
        left: 50%;
        -webkit-transform: translate(-50%, -50%);
        -moz-transform: translate(-50%, -50%);
        transform: translate(-50%, -50%);
    }

    .preloader-icon {
        width: 100px;
        height: 100px;
        display: inline-block;
        padding: 0px;
    }

    .preloader-icon span {
        position: absolute;
        display: inline-block;
        width: 100px;
        height: 100px;
        border-radius: 100%;
        /* background:#F7941D; */
        background: #191857;
        -webkit-animation: preloader-fx 1.6s linear infinite;
        animation: preloader-fx 1.6s linear infinite;
    }

    .preloader-icon span:last-child {
        animation-delay: -0.8s;
        -webkit-animation-delay: -0.8s;
    }

    @keyframes  preloader-fx {
        0% {
            transform: scale(0, 0);
            opacity: 0.5;
        }

        100% {
            transform: scale(1, 1);
            opacity: 0;
        }
    }

    @-webkit-keyframes preloader-fx {
        0% {
            -webkit-transform: scale(0, 0);
            opacity: 0.5;
        }

        100% {
            -webkit-transform: scale(1, 1);
            opacity: 0;
        }
    }

    /* End Preloader */
</style>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<style>
    #loader {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        background: rgba(0, 0, 0, 0.75) url('<?php echo e(asset('site_imgs/loading2.gif')); ?>') no-repeat center center;
        z-index: 10000;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
<script>
    /**Function to copy text */
    function copyText(element) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(element).html()).select();
        document.execCommand("copy");
        $('.tooltip .tooltip-inner').text('Copied!');
        $temp.remove();
      }
</script>
<script>
    /*Ajax setup CSRF*/
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
</script>
<script src="//code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bs-stepper/dist/js/bs-stepper.min.js"></script>
<script src="<?php echo e(asset('js/client-party/party-entity.js')); ?>"></script>
<script src="<?php echo e(asset('js/client-party/validation.js')); ?>"></script>
<script src="<?php echo e(asset('js/client-party/sub-form.js')); ?>"></script>
<script src="<?php echo e(asset('js/client-party/jquery-number/jquery.number.min.js')); ?>"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script>
    /** get user authorization status */
    window.User = {
        value: '<?php echo e(auth()->user()->can("edit-client-party")); ?>',
        edit: '<?php echo e($editForm); ?>'
    }
    window.approve_client_party = `<?php echo e($approve); ?>`;
</script>

<script src="<?php echo e(asset('js/client-party/show-create-client-party.js')); ?>"></script>
<script type="text/javascript">

   $('#close-modal').click(function(){
        $('#cancelForm')[0].reset();
        $('#cancelModal').modal('hide');
    });


    $('#submit-entity').on('click',function(){
        // validate form data
        let form_data = {
            id: `<?php echo e($clientParty->Id); ?>`,
            level: $('#level option:selected').val(),
            reason: $('#reason').val(),
        };

        if(form_data.level == ""){
            $('#level').addClass('is-invalid');
        }else if(form_data.reason == ""){
            $('#reason').addClass('is-invalid');
        }else{
            $.ajax({
                url: '/client-party/reject',
                type: 'POST',
                datatype: 'json',
                data: form_data,
                success: function(response){
                    console.log(response);
                    location.reload();
                }
            });
        }
    });

    $('#approveBtn').click(function(){
        let approve = confirm('Confirm Approval.');
        if (approve) {
            let form_data = {
                id: `<?php echo e($clientParty->Id); ?>`,
                status: `<?php echo e($clientParty->Status); ?>`,
            };
            $.ajax({
                url: '/client-party/approve',
                type: 'POST',
                datatype: 'json',
                data: form_data,
                success: function(response){
                    if(response[1] == 200){
                        alert("Entity Updated. !!");
                        window.location.href = `<?php echo e(route("client-party.approvals")); ?>`
                    }
                    /*location.reload();*/
                }
            });
        }
    });


    window.default_debtor = `<?php echo e($clientParty->DebtorType); ?>`;
    window.default_vendor = `<?php echo e($clientParty->VendorType); ?>`;


    /*check if select disabled ? append : select*/
    let debtor_val = (window.default_debtor == '' ? '' : window.default_debtor);
    if(!$('#debtorType').attr('disabled') && typeof $('#debtorType').attr('disabled') !== 'undefined'){
        $('#debtorType option').remove();
        $('#debtorType').append('<option selected>'+debtor_val+'</option>');
    }else{
         if (debtor_val == '') {
            $('#debtorType').attr('disabled','disabled');
        }else{
            console.log('saving debtor val');
            $('#debtorType').val(debtor_val).change();
        }
    }

    let vendor_val = (window.default_vendor  == '' ? '' : window.default_vendor );

    if(!$('#vendorType').attr('disabled') && typeof $('#vendorType').attr('disabled') !== 'undefined'){
        $('#vendorType option').remove();
        $('#vendorType').append('<option selected>'+vendor_val+'</option>');
    }else{
        if (vendor_val == '') {
            $('#vendorType').attr('disabled','disabled');
        }else{
            $('#vendorType').val(vendor_val);
        }
    }

    window.default_bill_curr = `<?php echo e($clientParty->BillingCurrencyId); ?>`;
</script>

<script type="text/javascript">
    let init = `<?php echo e($clientParty->attachments->count()+1); ?>`;
    let max_fields = 3;
    $(".addAttachment").click(function() {
        var lsthmtl = $(".clone").html();
        if (init < max_fields) {
            // check if file attachment i has content
            var names = document.getElementsByName('attachmentNames[]');
            var desc = document.getElementsByName('attachmentDescription[]');

                var fileInputName = names[names.length-2];
                var fileInputDescription = desc[desc.length-2];

                // -- validation --//
                // Allowing file type
                var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i;
                if (!allowedExtensions.exec(fileInputName.value) || fileInputDescription.value == '') {
                    alert('Either Invalid file type / No Description');
                    fileInputName.value = '';
                    return false;
                }else{
                    $("#attachClone").before(lsthmtl);
                    init++;
                }

        } else {
            alert('Maximum File Attachment Reached.');
        }
    });

    $("body").on("click", ".btn-danger", function() {
            $(this).parents(".hdtuto").remove();
            init--;
        });

    let spinner = $('#loader');

    $("body").on("click", ".deleteFile", function() {
            let id =  $(this).attr('id');
            let choice = confirm('Are you Sure You wish to delete file ?');
            let url = "/delete-client-party-attachment/"+id;
            if (choice) {
                spinner.show();
                $.ajax({
                    url: url,
                    type: 'POST',
                    success: function(response){
                        if (response[1] == 200) {
                            alert("File Deleted !!");
                            location.reload();
                        }
                    }
                });
            }
        });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('adminlte::page', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\inetpub\wwwroot\fit_service_desk\resources\views/client-party/show.blade.php ENDPATH**/ ?>