document.addEventListener("DOMContentLoaded", function () {
  const mobileMenuToggle = document.querySelector(".mobile-menu-toggle");
  const mobileMenu = document.getElementById("mobile-menu");

  if (mobileMenuToggle && mobileMenu) {
    mobileMenuToggle.addEventListener("click", function () {
      const isExpanded =
        mobileMenuToggle.getAttribute("aria-expanded") === "true";

      mobileMenuToggle.setAttribute("aria-expanded", String(!isExpanded));

      if (isExpanded) {
        mobileMenu.hidden = true;
        mobileMenu.classList.remove("is-open");
      } else {
        mobileMenu.hidden = false;
        mobileMenu.classList.add("is-open");
      }
    });
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
});