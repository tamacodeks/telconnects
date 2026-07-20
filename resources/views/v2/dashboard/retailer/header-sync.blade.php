@if($isRetailer)
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var lightLabel = document.querySelector('.v2-theme-option--light span:last-child');
      var darkLabel = document.querySelector('.v2-theme-option--dark span:last-child');
      var profileRole = document.querySelector('.v2-profile-copy small');
      if (lightLabel) lightLabel.textContent = @json($retailerText['theme_light']);
      if (darkLabel) darkLabel.textContent = @json($retailerText['theme_dark']);
      if (profileRole) profileRole.textContent = @json($retailerText['role']);
    });
  </script>
@endif
