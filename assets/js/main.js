(function () {
  var revealItems = document.querySelectorAll('.reveal, .reveal-stagger');
  var counterItems = document.querySelectorAll('[data-counter]');
  var parallaxItems = document.querySelectorAll('[data-parallax]');
  var menuToggle = document.querySelector('.menu-toggle');
  var headerTools = document.querySelector('#header-tools');
  var dashboardTabs = document.querySelectorAll('.dashboard-tab[data-tab-target]');
  var dashboardPanelsWrap = document.querySelector('.dashboard-panels');

  if (menuToggle && headerTools) {
    function closeMenu() {
      headerTools.classList.remove('is-open');
      menuToggle.classList.remove('is-open');
      menuToggle.setAttribute('aria-expanded', 'false');
      document.body.classList.remove('menu-open');
    }

    menuToggle.addEventListener('click', function () {
      var isOpen = headerTools.classList.toggle('is-open');
      menuToggle.classList.toggle('is-open', isOpen);
      menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      document.body.classList.toggle('menu-open', isOpen);
    });

    document.addEventListener('click', function (event) {
      if (!headerTools.classList.contains('is-open')) {
        return;
      }

      if (headerTools.contains(event.target) || menuToggle.contains(event.target)) {
        return;
      }

      closeMenu();
    });

    headerTools.addEventListener('click', function (event) {
      if (event.target.closest('a')) {
        closeMenu();
      }
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth > 900) {
        closeMenu();
      }
    });
  }

  if (dashboardTabs.length) {
    function activateDashboardTab(targetId) {
      var targetPanel = document.getElementById(targetId);
      var targetTab = document.querySelector('.dashboard-tab[data-tab-target="' + targetId + '"]');

      if (!targetPanel || !targetTab) {
        return;
      }

      dashboardTabs.forEach(function (currentTab) {
        currentTab.classList.remove('is-active');
      });

      document.querySelectorAll('.dashboard-panel').forEach(function (panel) {
        panel.classList.remove('is-active');
      });

      targetTab.classList.add('is-active');
      targetPanel.classList.add('is-active');
    }

    dashboardTabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        var targetId = tab.getAttribute('data-tab-target');
        activateDashboardTab(targetId);
      });
    });

    if (dashboardPanelsWrap) {
      var initialTab = dashboardPanelsWrap.getAttribute('data-initial-tab');
      if (initialTab) {
        activateDashboardTab(initialTab);
      }
    }

    if (window.location.hash && window.location.hash.indexOf('#tab-') === 0) {
      activateDashboardTab(window.location.hash.replace('#', ''));
    }

    var openRequestButton = document.getElementById('open-new-project-request');
    if (openRequestButton) {
      openRequestButton.addEventListener('click', function () {
        activateDashboardTab('tab-projects');
      });
    }
  }

  var serviceItemsContainer = document.querySelector('[data-service-items]');
  var addServiceButton = document.querySelector('[data-add-service-item]');
  var serviceTemplate = document.getElementById('service-item-template');
  var fullServiceToggle = document.querySelector('[data-full-service-toggle]');
  var fullServiceGoals = document.querySelector('[data-full-service-goals]');

  function updateServiceItemNames() {
    if (!serviceItemsContainer) {
      return;
    }

    var cards = serviceItemsContainer.querySelectorAll('[data-service-item]');
    cards.forEach(function (card, index) {
      var serviceField = card.querySelector('[data-name="service"]') || card.querySelector('select[name*="[service]"]');
      var actionsField = card.querySelector('[data-name="actions"]') || card.querySelector('select[name*="[actions]"]');
      var descriptionField = card.querySelector('[data-name="description"]') || card.querySelector('textarea[name*="[description]"]');

      if (serviceField) {
        serviceField.name = 'service_items[' + index + '][service]';
      }

      if (actionsField) {
        actionsField.name = 'service_items[' + index + '][actions][]';
      }

      if (descriptionField) {
        descriptionField.name = 'service_items[' + index + '][description]';
      }
    });
  }

  if (serviceItemsContainer) {
    updateServiceItemNames();
  }

  if (addServiceButton && serviceItemsContainer && serviceTemplate) {
    addServiceButton.addEventListener('click', function () {
      var fragment = serviceTemplate.content.cloneNode(true);
      serviceItemsContainer.appendChild(fragment);
      updateServiceItemNames();
    });
  }

  if (fullServiceToggle && fullServiceGoals) {
    fullServiceToggle.addEventListener('change', function () {
      fullServiceGoals.classList.toggle('is-visible', !!fullServiceToggle.checked);
    });
  }

  var adminServiceItemsContainer = document.querySelector('[data-admin-service-items]');
  var addAdminServiceButton = document.querySelector('[data-add-admin-service-item]');
  var adminServiceTemplate = document.getElementById('admin-service-item-template');
  var adminFullServiceToggle = document.querySelector('[data-admin-full-service-toggle]');
  var adminFullServiceGoals = document.querySelector('[data-admin-full-service-goals]');
  var adminRequestStatus = document.getElementById('admin_request_status');
  var adminDeclineReason = document.querySelector('[data-admin-decline-reason]');
  var adminNeedsBlock = document.querySelector('[data-admin-needs-block]');
  var adminNeedsContainer = document.querySelector('[data-admin-needs-container]');

  function updateAdminServiceItemNames() {
    if (!adminServiceItemsContainer) {
      return;
    }

    var cards = adminServiceItemsContainer.querySelectorAll('[data-admin-service-item]');
    cards.forEach(function (card, index) {
      var serviceField = card.querySelector('[data-admin-name="service"]') || card.querySelector('select[name*="[service]"]');
      var actionsField = card.querySelector('[data-admin-name="actions"]') || card.querySelector('select[name*="[actions]"]');
      var descriptionField = card.querySelector('[data-admin-name="description"]') || card.querySelector('textarea[name*="[description]"]');

      if (serviceField) {
        serviceField.name = 'admin_service_items[' + index + '][service]';
      }

      if (actionsField) {
        actionsField.name = 'admin_service_items[' + index + '][actions][]';
      }

      if (descriptionField) {
        descriptionField.name = 'admin_service_items[' + index + '][description]';
      }
    });
  }

  if (adminServiceItemsContainer) {
    updateAdminServiceItemNames();
  }

  if (addAdminServiceButton && adminServiceItemsContainer && adminServiceTemplate) {
    addAdminServiceButton.addEventListener('click', function () {
      var fragment = adminServiceTemplate.content.cloneNode(true);
      adminServiceItemsContainer.appendChild(fragment);
      updateAdminServiceItemNames();
    });
  }

  if (adminFullServiceToggle && adminFullServiceGoals) {
    adminFullServiceToggle.addEventListener('change', function () {
      adminFullServiceGoals.classList.toggle('is-visible', !!adminFullServiceToggle.checked);
    });
  }

  function ensureNeedsInputTail() {
    if (!adminNeedsContainer) {
      return;
    }

    var inputs = adminNeedsContainer.querySelectorAll('input[name="admin_need_fields[]"]');
    if (!inputs.length) {
      var firstInput = document.createElement('input');
      firstInput.type = 'text';
      firstInput.name = 'admin_need_fields[]';
      firstInput.placeholder = 'Add one need item';
      adminNeedsContainer.appendChild(firstInput);
      inputs = adminNeedsContainer.querySelectorAll('input[name="admin_need_fields[]"]');
    }

    var lastInput = inputs[inputs.length - 1];
    if (lastInput.value.trim() !== '') {
      var newInput = document.createElement('input');
      newInput.type = 'text';
      newInput.name = 'admin_need_fields[]';
      newInput.placeholder = 'Add one need item';
      adminNeedsContainer.appendChild(newInput);
    }
  }

  function refreshAdminDecisionFields() {
    if (!adminRequestStatus) {
      return;
    }

    var value = adminRequestStatus.value;

    if (adminDeclineReason) {
      adminDeclineReason.classList.toggle('is-visible', value === 'declined');
    }

    if (adminNeedsBlock) {
      adminNeedsBlock.classList.toggle('is-visible', value === 'in_need');
    }

    if (value === 'in_need') {
      ensureNeedsInputTail();
    }
  }

  if (adminRequestStatus) {
    adminRequestStatus.addEventListener('change', refreshAdminDecisionFields);
    refreshAdminDecisionFields();
  }

  if (adminNeedsContainer) {
    adminNeedsContainer.addEventListener('input', function (event) {
      if (event.target && event.target.matches('input[name="admin_need_fields[]"]')) {
        ensureNeedsInputTail();
      }
    });

    ensureNeedsInputTail();
  }

  var assetFilterButtons = document.querySelectorAll('[data-asset-filter]');
  var assetCards = document.querySelectorAll('[data-assets-list] .request-card[data-asset-kind]');
  var assetsEmptyMessage = document.querySelector('[data-assets-empty]');

  if (assetFilterButtons.length && assetCards.length) {
    function applyAssetFilter(filterValue) {
      var visibleCount = 0;

      assetCards.forEach(function (card) {
        var kind = card.getAttribute('data-asset-kind') || 'file';
        var shouldShow = filterValue === 'all' || kind === filterValue;
        card.classList.toggle('is-hidden', !shouldShow);

        if (shouldShow) {
          visibleCount += 1;
        }
      });

      if (assetsEmptyMessage) {
        assetsEmptyMessage.hidden = visibleCount !== 0;
      }
    }

    assetFilterButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        var filterValue = button.getAttribute('data-asset-filter') || 'all';

        assetFilterButtons.forEach(function (currentButton) {
          currentButton.classList.remove('is-active');
        });

        button.classList.add('is-active');
        applyAssetFilter(filterValue);
      });
    });

    applyAssetFilter('all');
  }

  var workspaceDecision = document.querySelector('[data-workspace-decision]');
  var workspaceNeedsBlock = document.querySelector('[data-workspace-needs-block]');
  var workspaceNeedsContainer = document.querySelector('[data-workspace-needs-container]');
  var workspaceStepsContainer = document.querySelector('[data-workspace-steps-container]');

  function createWorkspaceStepRow() {
    var row = document.createElement('label');
    row.className = 'need-item workspace-step-item';

    var checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.name = 'workspace_step_done[]';
    checkbox.value = '-1';

    var text = document.createElement('input');
    text.type = 'text';
    text.name = 'workspace_step_fields[]';
    text.placeholder = 'Add one execution step';

    row.appendChild(checkbox);
    row.appendChild(text);

    return row;
  }

  function normalizeWorkspaceStepRows() {
    if (!workspaceStepsContainer) {
      return;
    }

    var rows = workspaceStepsContainer.querySelectorAll('.workspace-step-item');
    if (!rows.length) {
      workspaceStepsContainer.appendChild(createWorkspaceStepRow());
      rows = workspaceStepsContainer.querySelectorAll('.workspace-step-item');
    }

    rows.forEach(function (row, index) {
      var checkbox = row.querySelector('input[type="checkbox"][name="workspace_step_done[]"]');
      if (checkbox) {
        checkbox.value = String(index);
      }
    });

    var lastRow = rows[rows.length - 1];
    var lastText = lastRow ? lastRow.querySelector('input[type="text"][name="workspace_step_fields[]"]') : null;

    if (lastText && lastText.value.trim() !== '') {
      workspaceStepsContainer.appendChild(createWorkspaceStepRow());
      normalizeWorkspaceStepRows();
    }
  }

  function ensureWorkspaceNeedsTail() {
    if (!workspaceNeedsContainer) {
      return;
    }

    var inputs = workspaceNeedsContainer.querySelectorAll('input[name="workspace_need_fields[]"]');
    if (!inputs.length) {
      var firstInput = document.createElement('input');
      firstInput.type = 'text';
      firstInput.name = 'workspace_need_fields[]';
      firstInput.placeholder = 'Add one required item';
      workspaceNeedsContainer.appendChild(firstInput);
      inputs = workspaceNeedsContainer.querySelectorAll('input[name="workspace_need_fields[]"]');
    }

    var lastInput = inputs[inputs.length - 1];
    if (lastInput && lastInput.value.trim() !== '') {
      var newInput = document.createElement('input');
      newInput.type = 'text';
      newInput.name = 'workspace_need_fields[]';
      newInput.placeholder = 'Add one required item';
      workspaceNeedsContainer.appendChild(newInput);
    }
  }

  function refreshWorkspaceDecisionFields() {
    if (!workspaceDecision || !workspaceNeedsBlock) {
      return;
    }

    var value = workspaceDecision.value;
    workspaceNeedsBlock.classList.toggle('is-visible', value === 'in_need');

    if (value === 'in_need') {
      ensureWorkspaceNeedsTail();
    }
  }

  if (workspaceStepsContainer) {
    workspaceStepsContainer.addEventListener('input', function (event) {
      if (event.target && event.target.matches('input[type="text"][name="workspace_step_fields[]"]')) {
        normalizeWorkspaceStepRows();
      }
    });

    normalizeWorkspaceStepRows();
  }

  if (workspaceDecision) {
    workspaceDecision.addEventListener('change', refreshWorkspaceDecisionFields);
    refreshWorkspaceDecisionFields();
  }

  if (workspaceNeedsContainer) {
    workspaceNeedsContainer.addEventListener('input', function (event) {
      if (event.target && event.target.matches('input[name="workspace_need_fields[]"]')) {
        ensureWorkspaceNeedsTail();
      }
    });
  }

  var timelineWraps = document.querySelectorAll('[data-timeline-wrap]');

  timelineWraps.forEach(function (timelineWrap) {
    var timelinePanel = timelineWrap.parentElement;
    if (!timelinePanel) {
      return;
    }

    var timelineButtons = timelineWrap.querySelectorAll('[data-timeline-filter]');
    var timelineList = timelinePanel.querySelector('[data-timeline-list]');
    var timelineItems = timelineList ? timelineList.querySelectorAll('li[data-timeline-type]') : [];
    var timelineEmpty = timelinePanel.querySelector('[data-timeline-empty]');

    if (!timelineButtons.length || !timelineItems.length) {
      return;
    }

    function applyTimelineFilter(filterValue) {
      var visibleCount = 0;

      timelineItems.forEach(function (item) {
        var itemType = item.getAttribute('data-timeline-type') || 'workspace';
        var isVisible = filterValue === 'all' || itemType === filterValue;
        item.classList.toggle('is-hidden', !isVisible);

        if (isVisible) {
          visibleCount += 1;
        }
      });

      if (timelineEmpty) {
        timelineEmpty.hidden = visibleCount !== 0;
      }
    }

    timelineButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        var filterValue = button.getAttribute('data-timeline-filter') || 'all';

        timelineButtons.forEach(function (currentButton) {
          currentButton.classList.remove('is-active');
        });

        button.classList.add('is-active');
        applyTimelineFilter(filterValue);
      });
    });

    applyTimelineFilter('all');
  });

  if (!('IntersectionObserver' in window) || !revealItems.length) {
    revealItems.forEach(function (item) {
      item.classList.add('is-visible');
    });
  } else {
    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            var delay = parseInt(entry.target.getAttribute('data-reveal-delay') || '0', 10);

            if (delay > 0) {
              entry.target.style.transitionDelay = (delay * 85) + 'ms';
            }

            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
          }
        });
      },
      {
        rootMargin: '0px 0px -12% 0px',
        threshold: 0.15
      }
    );

    revealItems.forEach(function (item) {
      observer.observe(item);
    });
  }

  if (counterItems.length) {
    counterItems.forEach(function (counter) {
      counter.textContent = '0';
    });

    var counterObserver = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) {
            return;
          }

          var node = entry.target;
          var maxValue = parseInt(node.getAttribute('data-counter') || '0', 10);
          var duration = 1200;
          var start = null;

          function tick(timeStamp) {
            if (!start) {
              start = timeStamp;
            }

            var progress = Math.min((timeStamp - start) / duration, 1);
            node.textContent = String(Math.floor(progress * maxValue));

            if (progress < 1) {
              window.requestAnimationFrame(tick);
            } else {
              node.textContent = String(maxValue);
            }
          }

          window.requestAnimationFrame(tick);
          counterObserver.unobserve(node);
        });
      },
      {
        threshold: 0.3
      }
    );

    counterItems.forEach(function (counter) {
      counterObserver.observe(counter);
    });
  }

  if (parallaxItems.length) {
    window.addEventListener('scroll', function () {
      var offset = window.scrollY || window.pageYOffset;

      parallaxItems.forEach(function (item) {
        var move = Math.min(offset * 0.08, 26);
        item.style.transform = 'translateY(' + move + 'px)';
      });
    }, { passive: true });
  }
})();
