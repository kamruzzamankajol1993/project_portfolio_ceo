(function () {
  'use strict';
  function initializeWidgets(root) {
    if (typeof flatpickr !== 'undefined') root.querySelectorAll('.js-date:not([data-calendar-ready])').forEach(el => {
      el.dataset.calendarReady='1'; flatpickr(el, {dateFormat:'Y-m-d', allowInput:true});
    });
    if (typeof Choices !== 'undefined') root.querySelectorAll('.js-choice:not([data-choices-ready])').forEach(el => {
      el.dataset.choicesReady='1'; new Choices(el, {removeItemButton:el.multiple,searchEnabled:el.options.length>5,shouldSort:false});
    });
    if (window.jQuery && jQuery.fn.summernote) jQuery(root).find('.js-summernote:not([data-summernote-ready])').each(function () {
      this.dataset.summernoteReady='1'; jQuery(this).summernote({height:145,toolbar:[['style',['bold','italic','underline']],['para',['ul','ol']],['insert',['link']],['view',['codeview']]]});
    });
  }
  initializeWidgets(document);

  document.addEventListener('submit', async e => {
    const form=e.target.closest('form[data-confirm]');
    if (!form || form.dataset.confirmed==='1') return;
    e.preventDefault();
    const message=form.dataset.confirm || 'Are you sure?';
    let accepted=false;
    if (window.Swal) {
      const result=await Swal.fire({title:message,icon:'warning',showCancelButton:true,confirmButtonText:form.dataset.confirmButton || 'Yes, delete',confirmButtonColor:form.dataset.confirmButton ? '#1682f8' : '#d9534f'});
      accepted=result.isConfirmed;
    } else accepted=window.confirm(message);
    if (accepted) { form.dataset.confirmed='1'; form.requestSubmit(); }
  });

  const addBtn=document.getElementById('add-permission');
  if (addBtn) addBtn.addEventListener('click',()=>{
    const row=document.createElement('div'); row.className='permission-entry';
    const input=document.createElement('input'); input.className='form-control';input.name='names[]';input.required=true;input.placeholder='e.g. articles.edit';
    const remove=document.createElement('button');remove.type='button';remove.className='module-secondary';remove.dataset.removePermission='1';remove.textContent='×';
    row.append(input,remove); document.getElementById('permission-inputs').append(row);input.focus();
  });
  document.addEventListener('click',e=>{
    const remove=e.target.closest('[data-remove-permission]');
    if (!remove) return;
    const list=document.getElementById('permission-inputs');
    if (list && list.querySelectorAll('.permission-entry').length>1) remove.closest('.permission-entry').remove();
  });

  const checkboxAll=document.getElementById('check-all-permissions');
  if (checkboxAll) {
    const refresh=()=>{
      const items=[...document.querySelectorAll('.permission-checkbox')];
      checkboxAll.checked=items.length>0 && items.every(i=>i.checked);
      checkboxAll.indeterminate=items.some(i=>i.checked) && !checkboxAll.checked;
      document.querySelectorAll('.permission-group').forEach(group=>{
        const values=[...group.querySelectorAll('.permission-checkbox')], control=group.querySelector('.group-checkbox');
        control.checked=values.length>0 && values.every(i=>i.checked);
        control.indeterminate=values.some(i=>i.checked) && !control.checked;
      });
    };
    checkboxAll.addEventListener('change',()=>{ document.querySelectorAll('.permission-checkbox').forEach(i=>i.checked=checkboxAll.checked);refresh(); });
    document.querySelectorAll('.group-checkbox').forEach(box=>box.addEventListener('change',()=>{
      box.closest('.permission-group').querySelectorAll('.permission-checkbox').forEach(i=>i.checked=box.checked);refresh();
    }));
    document.querySelectorAll('.permission-checkbox').forEach(box=>box.addEventListener('change',refresh));
    refresh();
  }

  const list = document.querySelector('[data-ajax-list]');
  if (list) {
    const form = document.querySelector('form[data-ajax-filter]');
    const search = form?.querySelector('input[name="search"]');
    let activeRequest = null;
    let debounceTimer = null;
    let requestVersion = 0;

    async function load(url, {replaceHistory = false, updateHistory = true} = {}) {
      const version = ++requestVersion;
      if (activeRequest) activeRequest.abort();
      activeRequest = new AbortController();
      list.classList.add('is-loading');
      list.setAttribute('aria-busy','true');
      try {
        const response = await fetch(url, {
          headers: {'X-Requested-With':'XMLHttpRequest','Accept':'text/html'},
          credentials: 'same-origin', signal: activeRequest.signal,
        });
        if (!response.ok) throw new Error('Unable to load list');
        const html = await response.text();
        if (version !== requestVersion) return;
        list.innerHTML = html;
        if (updateHistory) {
          if (replaceHistory) history.replaceState({ajaxList:true},'',url);
          else history.pushState({ajaxList:true},'',url);
        }
      } catch (error) {
        if (error.name !== 'AbortError' && version === requestVersion) {
          if (window.Swal) Swal.fire('Error', 'Unable to load the list. Please try again.', 'error');
          else alert('Unable to load the list. Please try again.');
        }
      } finally {
        if (version === requestVersion) {
          list.classList.remove('is-loading');
          list.removeAttribute('aria-busy');
        }
      }
    }

    function searchUrl() {
      const url = new URL(form.action, location.href);
      if (form) {
        new FormData(form).forEach((value, key) => {
          if (typeof value !== 'string') return;
          const item = value.trim();
          if (item) url.searchParams.set(key, item);
        });
      }
      // New filters always reset page to 1.
      return url.toString();
    }
    // Search immediately as the user types; debounce to reduce server load.
    if (search) search.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => load(searchUrl(), {replaceHistory:true}), 280);
    });
    if (form) {
      form.addEventListener('submit', e => {
        e.preventDefault();
        clearTimeout(debounceTimer);
        load(searchUrl(), {replaceHistory:true});
      });
      form.querySelectorAll('select').forEach(select => select.addEventListener('change', () => {
        clearTimeout(debounceTimer);
        load(searchUrl(), {replaceHistory:true});
      }));
    }

    document.addEventListener('click', e => {
      const link = e.target.closest('[data-ajax-list] .ajax-pagination a');
      if (!link) return;
      e.preventDefault();
      if (link.classList.contains('disabled') || link.getAttribute('aria-disabled') === 'true') return;
      clearTimeout(debounceTimer);
      load(link.href);
    });

    window.addEventListener('popstate', () => {
      clearTimeout(debounceTimer);
      if (form) {
        const current = new URL(location.href);
        form.querySelectorAll('input[name],select[name]').forEach(control => {
          if (control.type === 'hidden') return;
          control.value = current.searchParams.get(control.name) || '';
        });
      }
      load(location.href, {updateHistory:false});
    });
  }

  // Each sidebar group is a native accessible dropdown. Keep the current item visible.
  function syncSidebar(container, smooth = false) {
    if (!container) return;
    const links = [...container.querySelectorAll('.side-link')];
    const locationKey = location.pathname + location.hash;
    const planned = links.find(link => link.classList.contains('side-link-planned') &&
      new URL(link.href, location.href).pathname + new URL(link.href, location.href).hash === locationKey && !!location.hash);
    if (planned) {
      links.forEach(link => { link.classList.remove('active'); link.removeAttribute('aria-current'); });
      planned.classList.add('active'); planned.setAttribute('aria-current', 'page');
    }
    const active = planned || container.querySelector('.side-link.active');
    if (!active) return;
    const parent = active.closest('details.sidebar-group');
    if (parent) { parent.open = true; parent.classList.add('has-active'); }
    const parentBounds = container.getBoundingClientRect();
    const bounds = active.getBoundingClientRect();
    const delta = (bounds.top - parentBounds.top) - (container.clientHeight / 2) + (bounds.height / 2);
    container.scrollTo({ top: Math.max(0, container.scrollTop + delta), behavior: smooth ? 'smooth' : 'instant' });
  }
  document.querySelectorAll('[data-sidebar-scroll]').forEach(element => {
    if (element.closest('.offcanvas')) {
      element.closest('.offcanvas').addEventListener('shown.bs.offcanvas', () => syncSidebar(element, true));
    } else {
      requestAnimationFrame(() => syncSidebar(element));
    }
  });
  window.addEventListener('hashchange', () => {
    document.querySelectorAll('[data-sidebar-scroll]').forEach(el => syncSidebar(el, true));
  });
  document.querySelector('[data-seo-tab]')?.addEventListener('change', event => {
    window.location.assign(event.target.value);
  });
  document.querySelector('[data-extra-page-tab]')?.addEventListener('change', event => {
    window.location.assign(event.target.value);
  });

  // Keep blog slugs readable and URL-safe while allowing a manual override.
  const slugSource = document.querySelector('[data-slug-source]');
  const slugTarget = document.querySelector('[data-slug-target]');
  if (slugSource && slugTarget) {
    const toSlug = value => value.normalize('NFKD').replace(/[\u0300-\u036f]/g, '')
      .toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').replace(/-{2,}/g, '-');
    slugTarget.addEventListener('input', () => { slugTarget.dataset.manual = '1'; });
    slugSource.addEventListener('input', () => {
      if (slugTarget.dataset.manual !== '1') slugTarget.value = toSlug(slugSource.value);
    });
  }

  // Preserve the supplied topbar design and make its search affordance work.
  document.querySelector('.top-search-trigger')?.addEventListener('click', () => {
    document.querySelector('.global-search input')?.focus();
  });
  document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
      const target = document.querySelector('.global-search input');
      if (target) { e.preventDefault(); target.focus(); }
    }
  });
})();
