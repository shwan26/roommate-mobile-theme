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
});
