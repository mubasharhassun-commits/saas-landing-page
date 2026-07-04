import { Link } from "react-router-dom";
import { Wallet } from "lucide-react";

const columns = [
  {
    title: "Product",
    links: [
      { label: "Digital Invoice", href: "#" },
      { label: "Insights", href: "#" },
      { label: "Reimbursements", href: "#" },
      { label: "Virtual Assistant", href: "#" },
      { label: "Artificial Intelligence", href: "#" },
    ],
  },
  {
    title: "Company",
    links: [
      { label: "About Us", href: "/about" },
      { label: "Newsletters", href: "#" },
      { label: "Our Partners", href: "#" },
      { label: "Career", href: "#" },
      { label: "Contact Us", href: "/contact" },
    ],
  },
  {
    title: "Resources",
    links: [
      { label: "Blog", href: "/blog" },
      { label: "Pricing", href: "/pricing" },
      { label: "FAQ", href: "/faq" },
      { label: "Events", href: "#" },
      { label: "Ebook & Guide", href: "#" },
    ],
  },
  {
    title: "Follow Us",
    links: [
      { label: "LinkedIn", href: "#" },
      { label: "Twitter", href: "#" },
      { label: "Instagram", href: "#" },
      { label: "Facebook", href: "#" },
      { label: "YouTube", href: "#" },
    ],
  },
];

export function Footer() {
  return (
    <footer className="py-16">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <div className="grid grid-cols-2 gap-10 sm:grid-cols-3 lg:grid-cols-5">
          <div className="col-span-2 sm:col-span-3 lg:col-span-1">
            <Link to="/" className="flex items-center gap-2">
              <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-accent text-white">
                <Wallet className="h-4.5 w-4.5" />
              </span>
              <span className="text-lg font-semibold text-foreground">Spend.In</span>
            </Link>
            <p className="mt-4 text-sm text-muted-foreground">
              Data visualization, and expense management for your business.
            </p>
          </div>

          {columns.map((col) => (
            <div key={col.title}>
              <p className="text-sm font-semibold text-foreground">{col.title}</p>
              <ul className="mt-4 space-y-3">
                {col.links.map((link) =>
                  link.href.startsWith("/") ? (
                    <li key={link.label}>
                      <Link to={link.href} className="text-sm text-muted-foreground hover:text-foreground">
                        {link.label}
                      </Link>
                    </li>
                  ) : (
                    <li key={link.label}>
                      <a href={link.href} className="text-sm text-muted-foreground hover:text-foreground">
                        {link.label}
                      </a>
                    </li>
                  ),
                )}
              </ul>
            </div>
          ))}
        </div>

        <div className="mt-12 flex flex-col gap-4 border-t border-border pt-6 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex flex-wrap items-center gap-3 text-sm text-foreground">
            <a href="#" className="hover:text-accent">
              Privacy Policy
            </a>
            <span className="text-border">|</span>
            <a href="#" className="hover:text-accent">
              Terms & Conditions
            </a>
            <span className="text-border">|</span>
            <a href="#" className="hover:text-accent">
              Cookie Policy
            </a>
          </div>
          <p className="text-sm text-muted-foreground">© 2026 Spend.In. All rights reserved.</p>
        </div>
      </div>
    </footer>
  );
}
