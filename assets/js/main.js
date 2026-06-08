document.addEventListener("DOMContentLoaded", function () {
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle");
  const mobileMenu = document.getElementById("mobile-menu");
  const mobileMenuClose = document.querySelector(".mobile-menu-close");

  if (mobileMenuToggle && mobileMenu) {
    const setMobileMenu = function (isOpen) {
      mobileMenuToggle.setAttribute("aria-expanded", String(isOpen));
      mobileMenuToggle.setAttribute("aria-label", isOpen ? "Close menu" : "Open menu");

      if (isOpen) {
        mobileMenu.hidden = false;
        mobileMenu.classList.add("is-open");
      } else {
        mobileMenu.hidden = true;
        mobileMenu.classList.remove("is-open");
      }
    };

    mobileMenuToggle.addEventListener("click", function () {
      const isExpanded =
        mobileMenuToggle.getAttribute("aria-expanded") === "true";

      setMobileMenu(!isExpanded);
    });

    if (mobileMenuClose) {
      mobileMenuClose.addEventListener("click", function () {
        setMobileMenu(false);
      });
    }
  }

  const currentUrl = window.location.href;
  const bottomNavLinks = document.querySelectorAll(".mobile-bottom-nav__item");

  bottomNavLinks.forEach(function (link) {
    if (link.href === currentUrl) {
      link.classList.add("is-active");
    }
  });

  const filterForm = document.querySelector(".filter-form");
  if (filterForm) {
    const inputs = filterForm.querySelectorAll("input, select");

    inputs.forEach(function (input) {
      input.addEventListener("change", function () {
        input.classList.add("is-changed");
      });
    });
  }

  const listingCards = document.querySelectorAll(".listing-card");
  listingCards.forEach(function (card) {
    card.addEventListener("mouseenter", function () {
      card.classList.add("is-hovered");
    });

    card.addEventListener("mouseleave", function () {
      card.classList.remove("is-hovered");
    });
  });

  const deleteAccountModal = document.getElementById("rmt-delete-account-modal");
  const deleteAccountOpen = document.querySelector("[data-delete-account-open]");
  const deleteAccountCloseButtons = document.querySelectorAll("[data-delete-account-close]");
  const deleteAccountPassword = document.getElementById("rmt-delete-account-password");

  if (deleteAccountModal && deleteAccountOpen) {
    const setDeleteAccountModal = function (isOpen) {
      deleteAccountModal.hidden = !isOpen;
      document.body.classList.toggle("dashboard-delete-modal-open", isOpen);

      if (isOpen && deleteAccountPassword) {
        window.setTimeout(function () {
          deleteAccountPassword.focus();
        }, 0);
      }
    };

    deleteAccountOpen.addEventListener("click", function () {
      setDeleteAccountModal(true);
    });

    deleteAccountCloseButtons.forEach(function (button) {
      button.addEventListener("click", function () {
        setDeleteAccountModal(false);
      });
    });

    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape" && !deleteAccountModal.hidden) {
        setDeleteAccountModal(false);
      }
    });
  }

  const shareButtons = document.querySelectorAll(".js-listing-share");
  const shareCloseButtons = document.querySelectorAll("[data-share-close]");

  const setShareModal = function (modal, isOpen) {
    if (!modal) {
      return;
    }

    modal.hidden = !isOpen;
    modal.setAttribute("aria-hidden", String(!isOpen));
    document.body.classList.toggle("listing-share-modal-open", isOpen);

    if (isOpen) {
      const copyButton = modal.querySelector(".js-copy-share-link");

      window.setTimeout(function () {
        if (copyButton) {
          copyButton.focus();
        }
      }, 0);
    }
  };

  const copyShareUrl = function (url) {
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(url);
    }

    const input = document.createElement("textarea");
    input.value = url;
    input.setAttribute("readonly", "");
    input.style.position = "fixed";
    input.style.top = "-9999px";
    document.body.appendChild(input);
    input.select();
    document.execCommand("copy");
    document.body.removeChild(input);

    return Promise.resolve();
  };

  shareButtons.forEach(function (button) {
    button.addEventListener("click", async function () {
      const shareData = {
        title: button.dataset.shareTitle || document.title,
        text: button.dataset.shareText || "",
        url: button.dataset.shareUrl || window.location.href
      };

      if (navigator.share) {
        try {
          await navigator.share(shareData);
          return;
        } catch (error) {
          if (error && error.name === "AbortError") {
            return;
          }
        }
      }

      setShareModal(document.getElementById(button.dataset.shareModal), true);
    });
  });

  shareCloseButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      setShareModal(button.closest(".listing-share-modal"), false);
    });
  });

  document.querySelectorAll(".js-copy-share-link").forEach(function (button) {
    button.addEventListener("click", function () {
      const originalText = button.textContent;

      copyShareUrl(button.dataset.shareUrl || window.location.href).then(function () {
        button.textContent = "Copied";

        window.setTimeout(function () {
          button.textContent = originalText;
        }, 1600);
      });
    });
  });

  document.addEventListener("keydown", function (event) {
    if (event.key !== "Escape") {
      return;
    }

    document.querySelectorAll(".listing-share-modal:not([hidden])").forEach(function (modal) {
      setShareModal(modal, false);
    });
  });
});
