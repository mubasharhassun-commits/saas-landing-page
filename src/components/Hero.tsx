import { Link } from "react-router-dom";
import { Search, Bell, User } from "lucide-react";

const menu = ["Dashboard", "Transactions", "Invoices", "Reports", "Settings"];

const bars = [
  { month: "Jan", value: 38 },
  { month: "Feb", value: 46 },
  { month: "Mar", value: 40 },
  { month: "Apr", value: 55 },
  { month: "May", value: 48 },
  { month: "Jun", value: 70, highlighted: true },
  { month: "Jul", value: 52 },
  { month: "Aug", value: 60 },
  { month: "Sep", value: 44 },
  { month: "Oct", value: 58 },
  { month: "Nov", value: 50 },
  { month: "Dec", value: 63 },
];

export function Hero() {
  return (
    <section className="relative overflow-hidden">
      <div className="pointer-events-none absolute inset-0 -z-10">
        <div className="absolute -left-24 -top-24 h-72 w-72 rounded-full bg-accent-soft blur-3xl" />
        <div className="absolute -right-16 top-40 h-72 w-72 rounded-full bg-accent-soft blur-3xl" />
      </div>

      <div className="mx-auto max-w-3xl px-4 pt-16 text-center sm:px-6 sm:pt-24">
        <h1 className="text-4xl leading-tight font-bold tracking-tight text-foreground sm:text-6xl">
          All your business expenses in one place.
        </h1>
        <p className="mx-auto mt-6 max-w-xl text-base text-muted-foreground sm:text-lg">
          Track spending, manage invoices, and stay on top of your cash flow — all from
          one simple dashboard built for growing businesses.
        </p>
        <div className="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
          <a
            href="#"
            className="rounded-full bg-accent px-7 py-3 text-sm font-semibold text-white hover:bg-accent/90"
          >
            Get a Free Demo
          </a>
          <Link to="/pricing" className="text-sm font-semibold text-foreground hover:text-accent">
            See Pricing
          </Link>
        </div>
      </div>

      <div className="mx-auto mt-14 max-w-5xl px-4 pb-20 sm:px-6">
        <div className="rounded-2xl border border-border bg-section p-3 shadow-xl sm:p-4">
          <div className="rounded-xl border border-border bg-background p-4 sm:p-5">
            <div className="flex items-center justify-between gap-3">
              <span className="hidden h-8 w-24 rounded-full bg-section sm:block" />
              <div className="flex flex-1 items-center gap-2 rounded-full border border-border px-3 py-1.5 text-xs text-muted-foreground sm:max-w-xs">
                <Search className="h-3.5 w-3.5" />
                Search
              </div>
              <div className="flex items-center gap-3">
                <Bell className="h-4 w-4 text-muted-foreground" />
                <span className="flex h-7 w-7 items-center justify-center rounded-full bg-accent-soft text-accent">
                  <User className="h-3.5 w-3.5" />
                </span>
              </div>
            </div>

            <div className="mt-5 grid grid-cols-1 gap-5 md:grid-cols-[160px_1fr_180px]">
              <div className="hidden md:block">
                <p className="text-xs font-semibold text-muted-foreground">Main Menu</p>
                <div className="mt-3 flex flex-col gap-2">
                  {menu.map((item, i) => (
                    <span
                      key={item}
                      className={
                        i === 0
                          ? "rounded-lg bg-accent px-3 py-2 text-xs font-semibold text-white"
                          : "rounded-lg px-3 py-2 text-xs text-muted-foreground"
                      }
                    >
                      {item}
                    </span>
                  ))}
                </div>
              </div>

              <div>
                <p className="text-xs font-semibold text-muted-foreground">Expenses</p>
                <div className="relative mt-4 flex h-40 items-end gap-2">
                  {bars.map((bar) => (
                    <div key={bar.month} className="relative flex h-full flex-1 flex-col justify-end">
                      {bar.highlighted && (
                        <span className="absolute -top-8 left-1/2 -translate-x-1/2 rounded-md bg-accent px-2 py-1 text-[10px] font-semibold whitespace-nowrap text-white">
                          Expenses $15,030
                        </span>
                      )}
                      <div
                        className={
                          bar.highlighted
                            ? "rounded-t-sm bg-accent"
                            : "rounded-t-sm bg-accent-soft"
                        }
                        style={{ height: `${bar.value}%` }}
                      />
                    </div>
                  ))}
                </div>
                <div className="mt-2 flex gap-2 text-[10px] text-muted-foreground">
                  {bars.map((bar) => (
                    <span key={bar.month} className="flex-1 text-center">
                      {bar.month}
                    </span>
                  ))}
                </div>
              </div>

              <div className="rounded-xl border border-border p-4">
                <p className="text-xs text-muted-foreground">Balance</p>
                <p className="mt-1 text-xl font-bold text-foreground">
                  $120,435.00 <span className="text-xs font-medium text-muted-foreground">USD</span>
                </p>
                <div className="mt-4 flex flex-col gap-2">
                  <button className="rounded-lg bg-accent px-3 py-2 text-xs font-semibold text-white">
                    Top Up
                  </button>
                  <button className="rounded-lg border border-border px-3 py-2 text-xs font-semibold text-foreground">
                    Transfer
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
