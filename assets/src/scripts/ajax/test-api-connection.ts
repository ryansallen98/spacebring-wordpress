export default function testApiConnection() {
  return {
    status: "idle" as "idle" | "loading" | "success" | "error",
    response: null as null | unknown,

    async runTest() {
      this.status = "loading";

      try {
        const response = await fetch(SpacebringAjax.ajaxUrl, {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: new URLSearchParams({
            action: "spacebring_test_api_connection",
            _ajax_nonce: SpacebringAjax.nonces.testApiConnection,
          }),
        });

        const data = await response.json();

        console.log("data", data);

        this.status = data.success ? "success" : "error";
        this.response = data;
      } catch (e) {
        console.error("Error testing API connection:", e);
        this.status = "error";
        this.response = e;
      }
    },
  };
}
