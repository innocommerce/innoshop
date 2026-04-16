@php
  $showPopupNewsletter = system_setting('newsletter_popup_enabled', false);
@endphp

@if($showPopupNewsletter)
  <!-- Newsletter Popup Modal -->
  <div class="modal fade" id="newsletterPopupModal" tabindex="-1" aria-labelledby="newsletterPopupModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center px-4 pb-4">
          <div class="mb-3">
            <i class="bi bi-envelope-heart" style="font-size: 3rem; color: var(--bs-primary);"></i>
          </div>
          <h4 class="modal-title mb-3" id="newsletterPopupModalLabel">{{ __('front/newsletter.newsletter') }}</h4>
          <p class="text-muted mb-4">{{ __('front/newsletter.newsletter_desc') }}</p>
          <form class="newsletter-popup-form" action="{{ front_route('newsletter.subscribe') }}" method="POST">
            @csrf
            <input type="hidden" name="source" value="popup">
            <div class="mb-3">
              <div class="input-group">
                <input type="email" name="email" class="form-control form-control-lg newsletter-popup-email" 
                       placeholder="{{ __('front/newsletter.email_placeholder') }}" 
                       required>
                <button type="submit" class="btn btn-primary btn-lg newsletter-popup-submit">
                  <i class="bi bi-send"></i>
                </button>
              </div>
              <div class="form-check d-flex align-items-center mt-2">
                <input class="form-check-input me-2" type="checkbox" id="dontShowAgain">
                <label class="form-check-label text-muted small mb-0" for="dontShowAgain">
                  {{ __('front/newsletter.dont_show_again') }}
                </label>
              </div>
            </div>
            <div class="newsletter-popup-message"></div>
          </form>
        </div>
      </div>
    </div>
  </div>

  @push('footer')
  <script>
    // Wait for DOM and Bootstrap to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {
      // Double check Bootstrap is available
      if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap is not loaded');
        return;
      }
      
      const modalElement = document.getElementById('newsletterPopupModal');
      if (!modalElement) {
        console.error('Newsletter popup modal element not found');
        return;
      }
      
      const newsletterModal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: false
      });
      const popupDismissedKey = 'newsletter_popup_dismissed';
      
      // Check if user has dismissed the popup
      const popupDismissed = localStorage.getItem(popupDismissedKey);
      
      if (!popupDismissed) {
        // Show popup after 3 seconds
        setTimeout(function() {
          try {
            newsletterModal.show();
          } catch (e) {
            console.error('Error showing newsletter popup:', e);
          }
        }, 3000);
      }

      // Handle "Don't show again" checkbox
      $('#dontShowAgain').on('change', function() {
        if ($(this).is(':checked')) {
          localStorage.setItem(popupDismissedKey, 'true');
          newsletterModal.hide();
        }
      });

      // Handle modal close - save state if checkbox is checked
      modalElement.addEventListener('hidden.bs.modal', function() {
        if ($('#dontShowAgain').is(':checked')) {
          localStorage.setItem(popupDismissedKey, 'true');
        }
      });

      // Handle form submission
      $('.newsletter-popup-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $emailInput = $form.find('.newsletter-popup-email');
        const $submitBtn = $form.find('.newsletter-popup-submit');
        const $message = $form.find('.newsletter-popup-message');
        const email = $emailInput.val().trim();
        
        // Clear previous messages
        $message.removeClass('alert alert-success alert-danger').text('');
        
        if (!email) {
          $message.addClass('alert alert-danger').text('{{ __('front/newsletter.email_required') }}');
          return;
        }
        
        // Disable submit button
        const originalHtml = $submitBtn.html();
        $submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i>');
        
        axios.post($form.attr('action'), {
          email: email,
          source: $form.find('input[name="source"]').val() || 'popup',
          _token: '{{ csrf_token() }}'
        }).then(function(res) {
          if (res.success === true) {
            $message.addClass('alert alert-success').text(res.message || '{{ __('front/newsletter.subscribe_success') }}');
            $emailInput.val('');
            
            // Save state to prevent popup from showing again after successful subscription
            localStorage.setItem(popupDismissedKey, 'true');
            
            // Close modal after 2 seconds
            setTimeout(function() {
              newsletterModal.hide();
            }, 2000);
          } else {
            $message.addClass('alert alert-danger').text(res.message || '{{ __('front/newsletter.subscribe_failed') }}');
          }
        }).catch(function(error) {
          const errorMsg = error.response?.data?.message || error.message || '{{ __('front/newsletter.subscribe_failed') }}';
          $message.addClass('alert alert-danger').text(errorMsg);
        }).finally(function() {
          $submitBtn.prop('disabled', false).html(originalHtml);
        });
      });
    });
  </script>
  @endpush
@endif
