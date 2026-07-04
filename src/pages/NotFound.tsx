import { Link } from "react-router-dom";

export function NotFound() {
  return (
    <section className="flex flex-col items-center justify-center px-4 py-32 text-center">
      <p className="text-sm font-semibold text-accent">404</p>
      <h1 className="mt-3 text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
        Page not found
      </h1>
      <p className="mt-3 max-w-md text-muted-foreground">
        The page you're looking for doesn't exist or may have been moved.
      </p>
      <Link
        to="/"
        className="mt-8 inline-block rounded-full bg-accent px-6 py-3 text-sm font-semibold text-white hover:bg-accent/90"
      >
        Back to Home
      </Link>
    </section>
  );
}
