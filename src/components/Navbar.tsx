import { useState } from "react";
import { Link, NavLink } from "react-router-dom";
import { Menu, X, Wallet } from "lucide-react";
import { cn } from "@/lib/utils";

const links = [
  { label: "About", href: "/about" },
  { label: "Pricing", href: "/pricing" },
  { label: "Blog", href: "/blog" },
  { label: "FAQ", href: "/faq" },
  { label: "Contact", href: "/contact" },
];

export function Navbar() {
  const [open, setOpen] = useState(false);

  return (
    <header className="sticky top-0 z-50 border-b border-border bg-background/90 backdrop-blur">
      <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
        <Link to="/" className="flex items-center gap-2">
          <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-accent text-white">
            <Wallet className="h-4.5 w-4.5" />
          </span>
          <span className="text-lg font-semibold">Spend.In</span>
        </Link>

        <nav className="hidden items-center gap-8 lg:flex">
          {links.map((link) => (
            <NavLink
              key={link.label}
              to={link.href}
              className={({ isActive }) =>
                cn(
                  "text-sm font-medium hover:text-accent",
                  isActive ? "text-accent" : "text-foreground",
                )
              }
            >
              {link.label}
            </NavLink>
          ))}
        </nav>

        <div className="hidden items-center gap-5 lg:flex">
          <a href="#" className="text-sm font-medium text-muted-foreground hover:text-foreground">
            Login
          </a>
          <a
            href="#"
            className="rounded-full bg-accent px-5 py-2.5 text-sm font-semibold text-white hover:bg-accent/90"
          >
            Get Demo
          </a>
        </div>

        <button
          onClick={() => setOpen((v) => !v)}
          className="text-foreground lg:hidden"
          aria-label={open ? "Close menu" : "Open menu"}
        >
          {open ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
        </button>
      </div>

      {open && (
        <nav className="flex flex-col gap-1 border-t border-border px-4 py-3 lg:hidden">
          {links.map((link) => (
            <NavLink
              key={link.label}
              to={link.href}
              onClick={() => setOpen(false)}
              className={({ isActive }) =>
                cn(
                  "rounded-lg px-3 py-2.5 text-sm font-medium hover:bg-section",
                  isActive ? "text-accent" : "text-foreground",
                )
              }
            >
              {link.label}
            </NavLink>
          ))}
          <a href="#" className="rounded-lg px-3 py-2.5 text-sm font-medium text-muted-foreground">
            Login
          </a>
          <a
            href="#"
            className="mt-2 rounded-full bg-accent px-3 py-2.5 text-center text-sm font-semibold text-white"
          >
            Get Demo
          </a>
        </nav>
      )}
    </header>
  );
}
