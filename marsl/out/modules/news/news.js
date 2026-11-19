document.addEventListener("DOMContentLoaded", () => {
  const COOKIE_NAME = "allowThirdPartyContent";
  const PRIVACY_URL = "https://www.music2web.de/datenschutz-738";

  const style = document.createElement("style");
  style.textContent = `
    .social-toggle-switch {
      position: relative;
      display: inline-block;
      width: 40px;
      height: 22px;
      flex-shrink: 0;
    }
    .social-toggle-switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }
    .social-toggle-slider {
      position: absolute;
      cursor: pointer;
      top: 0; left: 0; right: 0; bottom: 0;
      background-color: #ccc;
      transition: 0.3s;
      border-radius: 22px;
    }
    .social-toggle-slider:before {
      position: absolute;
      content: "";
      height: 16px;
      width: 16px;
      left: 3px;
      bottom: 3px;
      background-color: white;
      transition: 0.3s;
      border-radius: 50%;
    }
    .social-toggle-switch input:checked + .social-toggle-slider {
      background-color: #e00000;
    }
    .social-toggle-switch input:checked + .social-toggle-slider:before {
      transform: translateX(18px);
    }
  `;
  document.head.appendChild(style);

  const embedTemplates = {
    spotify: (url) => {
      const iframe = document.createElement("iframe");
      iframe.style.borderRadius = "12px";
      iframe.src = url;
      iframe.width = "100%";
      iframe.height = "352";
      iframe.frameBorder = "0";
      iframe.allowFullscreen = true;
      iframe.allow =
        "autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture";
      iframe.loading = "lazy";
      return iframe;
    },
  };

  function hasConsent() {
    return document.cookie
      .split(";")
      .map((c) => c.trim())
      .some((c) => c === `${COOKIE_NAME}=1`);
  }

  function setConsentCookie() {
    const maxAge = 60 * 60 * 24 * 365;
    document.cookie = `${COOKIE_NAME}=1; path=/; max-age=${maxAge}; SameSite=Lax`;
  }

  function revokeConsentCookie() {
    document.cookie = `${COOKIE_NAME}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Lax`;
    document.cookie = `${COOKIE_NAME}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Lax`;
  }

  function createConsentPlaceholder(serviceName, url) {
    const box = document.createElement("div");
    box.className = "social-consent-box";
    box.dataset.service = serviceName;
    box.dataset.url = url;

    Object.assign(box.style, {
      border: "1px solid #ccc",
      borderRadius: "12px",
      padding: "24px",
      textAlign: "center",
      backgroundColor: "#f9f9f9",
      fontFamily: "sans-serif",
      margin: "16px 0",
      boxSizing: "border-box",
    });

    box.innerHTML = `
      <p style="margin-top: 0; color: #000000;">
        <strong>Inhalt von Drittanbieter blockiert</strong><br>
        Um diesen Inhalt anzuzeigen, musst du dem Laden von Inhalten von Drittanbietern zustimmen. 
        Dabei können Daten an Drittanbieter übertragen werden. 
        Weitere Informationen findest du in unserer 
        <a href="${PRIVACY_URL}" target="_blank">Datenschutzerklärung</a> unter dem Punkt <b>Inhalte von Drittanbietern</b>.
      </p>
    `;

    const button = document.createElement("button");
    button.type = "button";
    button.textContent = "Inhalte von Drittanbietern akzeptieren";
    Object.assign(button.style, {
      padding: "10px 20px",
      borderRadius: "6px",
      border: "none",
      backgroundColor: "#e00000",
      color: "#fff",
      fontWeight: "bold",
      cursor: "pointer",
      fontSize: "14px",
    });

    button.addEventListener("click", () => {
      setConsentCookie();
      updateAllElements(true);
    });

    box.appendChild(button);
    return box;
  }

  function createEmbedWithRevokeBar(service, url) {
    const wrapper = document.createElement("div");
    wrapper.className = "social-embed-wrapper";
    wrapper.dataset.service = service;
    wrapper.dataset.url = url;
    wrapper.style.width = "100%";
    wrapper.style.margin = "16px 0";

    const iframe = embedTemplates[service](url);
    wrapper.appendChild(iframe);

    const bar = document.createElement("div");
    Object.assign(bar.style, {
      display: "flex",
      justifyContent: "space-between",
      alignItems: "center",
      gap: "12px",
      width: "100%",
      boxSizing: "border-box",
      marginTop: "6px",
      padding: "8px 14px",
      backgroundColor: "#f4f4f4",
      border: "1px solid #e0e0e0",
      borderRadius: "8px",
      fontFamily: "Verdana, Arial, Helvetica, Sans-Serif",
      fontSize: "13px",
      color: "#000000",
    });

    const text = document.createElement("span");
    text.textContent =
      "Externe Inhalte sind aktiv. Du kannst deine Zustimmung jederzeit widerrufen:";

    const label = document.createElement("label");
    label.className = "social-toggle-switch";
    label.title = "Zustimmung widerrufen";

    const checkbox = document.createElement("input");
    checkbox.type = "checkbox";
    checkbox.checked = true;

    const slider = document.createElement("span");
    slider.className = "social-toggle-slider";

    checkbox.addEventListener("change", () => {
      if (!checkbox.checked) {
        revokeConsentCookie();
        updateAllElements(false);
      }
    });

    label.appendChild(checkbox);
    label.appendChild(slider);

    bar.appendChild(text);
    bar.appendChild(label);
    wrapper.appendChild(bar);

    return wrapper;
  }

  function updateAllElements(isAllowed) {
    const targets = document.querySelectorAll(
      "social, .social-consent-box, .social-embed-wrapper",
    );

    targets.forEach((el) => {
      const service = (
        el.getAttribute("service") || el.dataset.service
      )?.toLowerCase();
      const url = el.getAttribute("url") || el.dataset.url;

      if (!url || !service || !embedTemplates[service]) return;

      if (isAllowed) {
        if (!el.classList.contains("social-embed-wrapper")) {
          el.replaceWith(createEmbedWithRevokeBar(service, url));
        }
      } else {
        if (!el.classList.contains("social-consent-box")) {
          el.replaceWith(createConsentPlaceholder(service, url));
        }
      }
    });
  }

  updateAllElements(hasConsent());
});
