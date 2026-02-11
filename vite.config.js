import { defineConfig } from "vite";
import tailwindcss from "@tailwindcss/vite";
import path from "path";

export default () => {
  const scriptsDir = path.resolve(__dirname, "assets/src/scripts");
  const stylesDir = path.resolve(__dirname, "assets/src/styles");

  return defineConfig({
    plugins: [tailwindcss()],
    resolve: {
      alias: {
        "@scripts": scriptsDir,
        "@styles": stylesDir,
      },
    },
    build: {
      outDir: path.resolve(__dirname, "assets/build"),
      emptyOutDir: true,
      manifest: true,
      rollupOptions: {
        input: {
            tailwind: path.resolve(stylesDir, "tailwind.css"),
            admin: path.resolve(scriptsDir, "admin.ts"),
        },
        output: {
          entryFileNames: "js/[name].js",
          chunkFileNames: "js/[name].js",
          assetFileNames: (assetInfo) => {
            if (assetInfo.name?.endsWith(".css")) {
              return "css/[name][extname]";
            }
            return "assets/[name][extname]";
          },
        },
      },
    },
  });
};
