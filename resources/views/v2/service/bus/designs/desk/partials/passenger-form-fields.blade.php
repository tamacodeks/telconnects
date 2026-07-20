@php
    $fieldPrefix = $prefix . 'Passenger' . $key;
    $firstNameField      = $fieldPrefix . 'FirstName';
    $lastNameField       = $fieldPrefix . 'LastName';
    $birthdateField      = $fieldPrefix . 'Birthdate';
    $genderField         = $fieldPrefix . 'Gender';
    $emailField          = $fieldPrefix . 'Email';
    $phoneField          = $fieldPrefix . 'Phone';
    $citizenshipField    = $fieldPrefix . 'Citizenship';
    $passportField       = $fieldPrefix . 'Passport';
    $passportExpiryField = $fieldPrefix . 'PassportExpiry';
    $visaField           = $fieldPrefix . 'Visa';
@endphp

{{-- Personal details --}}
<div class="bco-section">
    <div class="bco-section-head">
        <span class="bco-section-icon"><i class="fas fa-user"></i></span>
        <h4>{{ __('bus.checkout.passenger_details') }}</h4>
    </div>
    <div class="bco-grid">

        <div class="bco-field">
            <label class="bco-label" for="{{ $firstNameField }}">{{ __('bus.checkout.first_name') }}</label>
            <div class="bco-input-icon-wrap">
                <i class="fas fa-user bco-input-icon"></i>
                <input type="text" id="{{ $firstNameField }}"
                    class="bco-input bco-input--icon bus-v2-control"
                    name="firstname[]"
                    value="{{ old('firstname.' . $key, $passenger['first_name'] ?? '') }}"
                    data-error-key="firstname.{{ $key }}"
                    autocomplete="given-name"
                    placeholder="{{ __('bus.checkout.first_name') }}"
                    aria-describedby="{{ $firstNameField }}Error"
                    aria-invalid="false">
            </div>
            <span id="{{ $firstNameField }}Error" class="bco-field-err bus-v2-field-error" data-error-for="firstname.{{ $key }}" aria-live="polite"></span>
        </div>

        <div class="bco-field">
            <label class="bco-label" for="{{ $lastNameField }}">{{ __('bus.checkout.last_name') }}</label>
            <div class="bco-input-icon-wrap">
                <i class="fas fa-user bco-input-icon"></i>
                <input type="text" id="{{ $lastNameField }}"
                    class="bco-input bco-input--icon bus-v2-control"
                    name="lastname[]"
                    value="{{ old('lastname.' . $key, $passenger['last_name'] ?? '') }}"
                    data-error-key="lastname.{{ $key }}"
                    autocomplete="family-name"
                    placeholder="{{ __('bus.checkout.last_name') }}"
                    aria-describedby="{{ $lastNameField }}Error"
                    aria-invalid="false">
            </div>
            <span id="{{ $lastNameField }}Error" class="bco-field-err bus-v2-field-error" data-error-for="lastname.{{ $key }}" aria-live="polite"></span>
        </div>

        <div class="bco-field">
            <label class="bco-label" for="{{ $birthdateField }}">{{ __('bus.checkout.date_of_birth') }}</label>
            <div class="bco-input-icon-wrap">
                <i class="fas fa-calendar-alt bco-input-icon"></i>
                <input type="text" id="{{ $birthdateField }}"
                    class="bco-input bco-input--icon bus-v2-birthdate js-bus-v2-datepicker"
                    data-passenger-kind="{{ $isAdult ? 'adult' : 'child' }}"
                    data-datepicker-kind="birthdate"
                    name="birthdate[]"
                    value="{{ $birthdateValue }}"
                    autocomplete="bday"
                    readonly
                    data-error-key="birthdate.{{ $key }}"
                    placeholder="DD.MM.YYYY"
                    aria-describedby="{{ $birthdateField }}Error"
                    aria-invalid="false">
            </div>
            <span id="{{ $birthdateField }}Error" class="bco-field-err bus-v2-field-error" data-error-for="birthdate.{{ $key }}" aria-live="polite"></span>
        </div>

        <div class="bco-field">
            <label class="bco-label" for="{{ $genderField }}">{{ __('bus.checkout.gender') }}</label>
            <div class="bco-select-wrap">
                <select id="{{ $genderField }}"
                    class="bco-select bus-v2-select"
                    name="gender[]"
                    data-error-key="gender.{{ $key }}"
                    aria-describedby="{{ $genderField }}Error"
                    aria-invalid="false">
                    <option value="">{{ __('bus.checkout.select_option') }}</option>
                    <option value="male"   {{ old('gender.' . $key) === 'male'   ? 'selected' : '' }}>{{ __('bus.gender.male') }}</option>
                    <option value="female" {{ old('gender.' . $key) === 'female' ? 'selected' : '' }}>{{ __('bus.gender.female') }}</option>
                </select>
                <i class="fas fa-chevron-down bco-select-caret"></i>
            </div>
            <span id="{{ $genderField }}Error" class="bco-field-err bus-v2-field-error" data-error-for="gender.{{ $key }}" aria-live="polite"></span>
        </div>

    </div>
</div>

{{-- Contact details --}}
<div class="bco-section">
    <div class="bco-section-head">
        <span class="bco-section-icon"><i class="fas fa-address-card"></i></span>
        <h4>{{ __('bus.checkout.contact_details') }}</h4>
    </div>
    <div class="bco-grid">

        <div class="bco-field">
            <label class="bco-label" for="{{ $emailField }}">{{ __('bus.checkout.email') }}</label>
            <div class="bco-input-icon-wrap">
                <i class="fas fa-envelope bco-input-icon"></i>
                <input type="email" id="{{ $emailField }}"
                    class="bco-input bco-input--icon bus-v2-control"
                    name="email[]"
                    value="{{ old('email.' . $key, $passenger['email'] ?? '') }}"
                    data-error-key="email.{{ $key }}"
                    autocomplete="email"
                    placeholder="name@example.com"
                    aria-describedby="{{ $emailField }}Error"
                    aria-invalid="false">
            </div>
            <span id="{{ $emailField }}Error" class="bco-field-err bus-v2-field-error" data-error-for="email.{{ $key }}" aria-live="polite"></span>
        </div>

        <div class="bco-field bco-field--phone">
            <label class="bco-label" for="{{ $phoneField }}">{{ __('bus.checkout.phone_number') }}</label>
            <div class="bco-input-icon-wrap">
                <i class="fas fa-phone bco-input-icon"></i>
                <input type="tel" id="{{ $phoneField }}"
                    class="bco-input bco-input--icon bus-v2-phone-input"
                    value=""
                    inputmode="tel"
                    autocomplete="tel-national"
                    placeholder="06 12 34 56 78"
                    data-phone-visible
                    data-phone-hidden="#{{ $phoneHiddenField }}"
                    data-error-key="phone_number.{{ $key }}"
                    aria-describedby="{{ $phoneField }}Error"
                    aria-invalid="false">
                <input type="hidden" id="{{ $phoneHiddenField }}" name="phone_number[]" value="{{ $phoneValue }}" data-phone-hidden>
            </div>
            <span id="{{ $phoneField }}Error" class="bco-field-err bus-v2-field-error" data-error-for="phone_number.{{ $key }}" aria-live="polite"></span>
        </div>

    </div>
</div>

{{-- Travel document --}}
<div class="bco-section">
    <div class="bco-section-head">
        <span class="bco-section-icon"><i class="fas fa-passport"></i></span>
        <h4>{{ __('bus.checkout.travel_document') }}</h4>
    </div>
    <div class="bco-grid">

        <div class="bco-field">
            <label class="bco-label" for="{{ $citizenshipField }}">{{ __('bus.checkout.citizenship') }}</label>
            <div class="bco-select-wrap">
                <select id="{{ $citizenshipField }}"
                    class="bco-select bus-v2-select bus-v2-citizenship"
                    name="citizenship[]"
                    data-target="{{ $issuingField }}"
                    data-error-key="citizenship.{{ $key }}"
                    aria-describedby="{{ $citizenshipField }}Error"
                    aria-invalid="false">
                    <option value="">{{ __('bus.checkout.select_option') }}</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country->iso }}" data-iso3="{{ $country->iso3 }}"
                            {{ old('citizenship.' . $key) === $country->iso ? 'selected' : '' }}>
                            {{ $country->name }}
                        </option>
                    @endforeach
                </select>
                <i class="fas fa-chevron-down bco-select-caret"></i>
            </div>
            <span id="{{ $citizenshipField }}Error" class="bco-field-err bus-v2-field-error" data-error-for="citizenship.{{ $key }}" aria-live="polite"></span>
        </div>

        <div class="bco-field">
            <label class="bco-label" for="{{ $passportField }}">{{ __('bus.checkout.passport_number') }}</label>
            <div class="bco-input-icon-wrap">
                <i class="fas fa-passport bco-input-icon"></i>
                <input type="text" id="{{ $passportField }}"
                    class="bco-input bco-input--icon bus-v2-control"
                    name="identification_number[]"
                    value="{{ old('identification_number.' . $key) }}"
                    data-error-key="identification_number.{{ $key }}"
                    autocomplete="off"
                    placeholder="Passport number"
                    aria-describedby="{{ $passportField }}Error"
                    aria-invalid="false">
            </div>
            <span id="{{ $passportField }}Error" class="bco-field-err bus-v2-field-error" data-error-for="identification_number.{{ $key }}" aria-live="polite"></span>
        </div>

        <div class="bco-field">
            <label class="bco-label" for="{{ $passportExpiryField }}">{{ __('bus.checkout.passport_expiry') }}</label>
            <div class="bco-input-icon-wrap">
                <i class="fas fa-calendar-check bco-input-icon"></i>
                <input type="text" id="{{ $passportExpiryField }}"
                    class="bco-input bco-input--icon bus-v2-expirydate js-bus-v2-datepicker"
                    data-datepicker-kind="expiry"
                    name="identification_expiry_date[]"
                    value="{{ $expiryValue }}"
                    autocomplete="off"
                    readonly
                    data-error-key="identification_expiry_date.{{ $key }}"
                    placeholder="DD.MM.YYYY"
                    aria-describedby="{{ $passportExpiryField }}Error"
                    aria-invalid="false">
            </div>
            <span id="{{ $passportExpiryField }}Error" class="bco-field-err bus-v2-field-error" data-error-for="identification_expiry_date.{{ $key }}" aria-live="polite"></span>
        </div>

        <div class="bco-field">
            <label class="bco-label" for="{{ $visaField }}">{{ __('bus.checkout.visa_or_permit') }}</label>
            <div class="bco-select-wrap">
                <select id="{{ $visaField }}"
                    class="bco-select bus-v2-select"
                    name="visa_permit_type[]"
                    data-error-key="visa_permit_type.{{ $key }}"
                    aria-describedby="{{ $visaField }}Error"
                    aria-invalid="false">
                    <option value="">{{ __('bus.checkout.select_option') }}</option>
                    <option value="single_or_double_entry_visa"    {{ old('visa_permit_type.' . $key) === 'single_or_double_entry_visa'    ? 'selected' : '' }}>{{ __('bus.visa_permit.single_or_double_entry_visa') }}</option>
                    <option value="multiple_entry_visa"            {{ old('visa_permit_type.' . $key) === 'multiple_entry_visa'            ? 'selected' : '' }}>{{ __('bus.visa_permit.multiple_entry_visa') }}</option>
                    <option value="eu_citizenship"                 {{ old('visa_permit_type.' . $key) === 'eu_citizenship'                 ? 'selected' : '' }}>{{ __('bus.visa_permit.eu_citizenship') }}</option>
                    <option value="eu_residence_permit"            {{ old('visa_permit_type.' . $key) === 'eu_residence_permit'            ? 'selected' : '' }}>{{ __('bus.visa_permit.eu_residence_permit') }}</option>
                    <option value="eu_family_with_residence_card"  {{ old('visa_permit_type.' . $key) === 'eu_family_with_residence_card'  ? 'selected' : '' }}>{{ __('bus.visa_permit.eu_family_with_residence_card') }}</option>
                    <option value="local_border_permit"            {{ old('visa_permit_type.' . $key) === 'local_border_permit'            ? 'selected' : '' }}>{{ __('bus.visa_permit.local_border_permit') }}</option>
                    <option value="long_stay_visa"                 {{ old('visa_permit_type.' . $key) === 'long_stay_visa'                 ? 'selected' : '' }}>{{ __('bus.visa_permit.long_stay_visa') }}</option>
                    <option value="diplomat_or_high_ranking_official" {{ old('visa_permit_type.' . $key) === 'diplomat_or_high_ranking_official' ? 'selected' : '' }}>{{ __('bus.visa_permit.diplomat_or_high_ranking_official') }}</option>
                    <option value="refugee_or_person_in_need"      {{ old('visa_permit_type.' . $key) === 'refugee_or_person_in_need'      ? 'selected' : '' }}>{{ __('bus.visa_permit.refugee_or_person_in_need') }}</option>
                </select>
                <i class="fas fa-chevron-down bco-select-caret"></i>
            </div>
            <span id="{{ $visaField }}Error" class="bco-field-err bus-v2-field-error" data-error-for="visa_permit_type.{{ $key }}" aria-live="polite"></span>
        </div>

    </div>
</div>
