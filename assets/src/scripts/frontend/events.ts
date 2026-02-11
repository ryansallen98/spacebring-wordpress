import { Calendar } from "@fullcalendar/core";
import dayGridPlugin from "@fullcalendar/daygrid";

declare global {
  interface Window {
    SpacebringData?: {
      events: Array<{
        id?: string;
        title: string;
        start: string;
        end?: string;
        url: string;
      }>;
    };
  }
}

let calendar: Calendar | null = null;

function initCalendar(): void {
  if (calendar) return; // prevent double init

  const el = document.getElementById("spacebring-calendar");
  const events = window.SpacebringData?.events ?? [];

  if (!el) return;

  calendar = new Calendar(el, {
    plugins: [dayGridPlugin],
    initialView: "dayGridMonth",
    height: "auto",
    events,
    eventClick(info) {
      info.jsEvent.preventDefault();
      if (info.event.url) {
        window.open(info.event.url, "_blank");
      }
    },
  });

  calendar.render();
}

document.addEventListener("DOMContentLoaded", () => {
  const tabs = document.querySelectorAll<HTMLButtonElement>(".spacebring-tab");
  const listView = document.querySelector<HTMLElement>(".spacebring-view-list");
  const calendarView = document.querySelector<HTMLElement>(".spacebring-view-calendar");

  if (!listView || !calendarView) return;

  tabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      const view = tab.dataset.view;

      // Reset all tabs
      tabs.forEach((t) => t.setAttribute("data-active", "false"));

      // Activate current tab
      tab.setAttribute("data-active", "true");

      if (view === "list") {
        listView.classList.remove("hidden");
        calendarView.classList.add("hidden");
      } else if (view === "calendar") {
        listView.classList.add("hidden");
        calendarView.classList.remove("hidden");

        // 👇 Initialize calendar when it becomes visible
        initCalendar();
      }
    });
  });
});