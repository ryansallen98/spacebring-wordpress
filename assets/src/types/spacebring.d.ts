export {};

declare global {
  interface Window {
    SpacebringAjax: {
      ajaxUrl: string;
      nonces: {
        testApi: string;
        [key: string]: string;
      };
    };
  }

  const SpacebringAjax: Window["SpacebringAjax"];
}