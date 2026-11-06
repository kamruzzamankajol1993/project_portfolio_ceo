@extends('website.master.master')

@section('title', $extraPage->meta_title ?: $extraPage->title . ' | ' . $siteSetting->site_name)

@section('css')
<link rel="stylesheet" href="{{ asset('public/site/css/content.css') }}">
@endsection

@section('body')
<main class="site-content-wrap contact-page-wrap">
  <section class="content-hero contact-hero">
    <span class="content-kicker"><i class="bi bi-chat-dots-fill"></i> LET'S CONNECT</span>
    <h1>{{ $extraPage->title }}</h1>
    <p>{{ $extraPage->meta_description ?: 'Have a question, correction, profile suggestion or partnership idea? Send our team a message.' }}</p>
  </section>

  <section class="contact-page-grid">
    <article class="contact-form-card">
      <div class="contact-card-heading">
        <span class="contact-form-icon"><i class="bi bi-send-fill"></i></span>
        <div><span class="content-kicker">SEND AN ENQUIRY</span><h2>How can we help?</h2><p>Complete the form and our team will review your message.</p></div>
      </div>
      @if(trim(strip_tags($extraPage->content ?? '')) !== '')
        <div class="contact-intro-copy">{!! $extraPage->content !!}</div>
      @endif
      <form id="publicContactForm" class="public-contact-form" method="post" action="{{ route('site.contact.submit') }}" data-server-form novalidate>
        @csrf
        <div class="contact-form-grid">
          <label>Full name <span>*</span><input class="form-control" type="text" name="name" maxlength="180" autocomplete="name" placeholder="Your name" required></label>
          <label>Email address <span>*</span><input class="form-control" type="email" name="email" maxlength="191" autocomplete="email" placeholder="you@example.com" required></label>
          <label>Phone number <small>Optional</small><input class="form-control" type="tel" name="phone" maxlength="30" autocomplete="tel" placeholder="Your phone number"></label>
          <label>Subject <span>*</span><input class="form-control" type="text" name="subject" maxlength="180" placeholder="What is this about?" required></label>
          <label class="contact-form-wide">Your message <span>*</span><textarea class="form-control" name="message" rows="5" minlength="10" maxlength="5000" placeholder="Write your message here..." required></textarea><small class="contact-field-hint">Please include enough detail for us to understand your enquiry.</small></label>
        </div>
        <div class="contact-honeypot" aria-hidden="true"><label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
        <div class="contact-submit-row"><small><i class="bi bi-lock-fill"></i> Your information is used only to respond to your enquiry.</small><button class="btn contact-submit-btn" type="submit"><span>Send message</span><i class="bi bi-arrow-right"></i></button></div>
      </form>
    </article>

    <aside class="contact-details-card">
      <span class="content-kicker">CONTACT INFORMATION</span>
      <h2>Reach our team</h2>
      <p class="contact-details-intro">You can also contact us directly using the details below.</p>
      @if($siteSetting->contact_email)
        <a class="contact-detail-row contact-detail-email" href="mailto:{{ $siteSetting->contact_email }}"><span><i class="bi bi-envelope-fill"></i></span><div><small>Email address</small><strong>{{ $siteSetting->contact_email }}</strong></div><i class="bi bi-arrow-up-right contact-detail-arrow"></i></a>
      @endif
      @if($siteSetting->contact_phone)
        <a class="contact-detail-row contact-detail-phone" href="tel:{{ preg_replace('/[^0-9+]/', '', $siteSetting->contact_phone) }}"><span><i class="bi bi-telephone-fill"></i></span><div><small>Phone number</small><strong>{{ $siteSetting->contact_phone }}</strong></div><i class="bi bi-arrow-up-right contact-detail-arrow"></i></a>
      @endif
      @if($siteSetting->address)
        <div class="contact-detail-row contact-detail-address"><span><i class="bi bi-geo-alt-fill"></i></span><div><small>Office address</small><strong>{{ $siteSetting->address }}</strong></div></div>
      @endif
      @if(!$siteSetting->contact_email && !$siteSetting->contact_phone && !$siteSetting->address)
        <div class="contact-details-empty"><i class="bi bi-info-circle"></i><span>Contact details will appear here after they are added in Admin → Site Settings → Contact Information.</span></div>
      @endif
      <div class="contact-aside-bottom"><i class="bi bi-shield-check"></i><div><strong>Need a profile?</strong><span>Create an account first. Profile requests can be submitted after your account is verified and approved.</span><a href="{{ route('customer.register') }}">Create an account <i class="bi bi-arrow-right"></i></a></div></div>
    </aside>
  </section>
</main>
@endsection

@section('scripts')
<script>
(() => {
  const form = document.getElementById('publicContactForm');
  if (!form) return;

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;

    const button = form.querySelector('button[type="submit"]');
    const original = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span>Sending...</span><i class="bi bi-arrow-repeat"></i>';

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });
      const result = await response.json().catch(() => ({}));

      if (!response.ok) {
        const firstError = result.errors ? Object.values(result.errors).flat()[0] : (result.message || 'Unable to send your message. Please try again.');
        if (window.Swal) {
          await Swal.fire({icon: 'error', title: 'Please check your message', text: firstError, confirmButtonColor: '#5367df'});
        } else {
          window.alert(firstError);
        }
        return;
      }

      form.reset();
      if (window.Swal) {
        await Swal.fire({icon: 'success', title: 'Message sent!', text: result.message || 'Thank you for contacting us. Our team will review your message shortly.', confirmButtonColor: '#5367df'});
      } else {
        window.alert(result.message || 'Your message has been sent.');
      }
    } catch (error) {
      const errorText = 'A network error occurred. Please check your connection and try again.';
      if (window.Swal) Swal.fire({icon: 'error', title: 'Message not sent', text: errorText, confirmButtonColor: '#5367df'});
      else window.alert(errorText);
    } finally {
      button.disabled = false;
      button.innerHTML = original;
    }
  });
})();
</script>
@endsection
