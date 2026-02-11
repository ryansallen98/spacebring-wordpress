export default function synchronization() {
  return {
    status: "idle" as "idle" | "loading" | "success" | "error",
    logs: [] as any[],

    runTest() {
      this.status = "loading";
      this.logs = [];

      const url =
        SpacebringAjax.ajaxUrl +
        "?action=spacebring_synchronization_stream" +
        "&_ajax_nonce=" +
        SpacebringAjax.nonces.synchronization;

      const source = new EventSource(url);

      source.onmessage = (event) => {
        const data = JSON.parse(event.data);

        if (data.done) {
          this.status = "success";
          source.close();
          return;
        }

        this.logs.push(data);
      };

      source.onerror = () => {
        this.status = "error";
        source.close();
      };
    },
  };
}
