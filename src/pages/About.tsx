import { Link } from "react-router-dom";
import { Target, Users, Sparkles, ShieldCheck } from "lucide-react";

const stats = [
  { label: "Businesses using Spend.In", value: "12,000+" },
  { label: "Expenses tracked monthly", value: "$480M+" },
  { label: "Countries", value: "34" },
  { label: "Founded", value: "2022" },
];

const values = [
  {
    icon: Target,
    title: "Clarity first",
    description: "We believe every business owner deserves to know exactly where their money is going, without digging through spreadsheets.",
  },
  {
    icon: Users,
    title: "Built with our users",
    description: "Every feature we ship starts as a request from a real finance team. We build for the people who use Spend.In daily.",
  },
  {
    icon: Sparkles,
    title: "Simple by default",
    description: "Powerful doesn't have to mean complicated. We work hard to keep the product simple, even as it grows more capable.",
  },
  {
    icon: ShieldCheck,
    title: "Trust is earned",
    description: "Your financial data is sensitive. We take security seriously so you never have to think twice about it.",
  },
];

export function About() {
  return (
    <>
      <section className="py-20">
        <div className="mx-auto max-w-3xl px-4 text-center sm:px-6">
          <p className="text-xs font-semibold tracking-wide text-accent uppercase">About Us</p>
          <h1 className="mt-3 text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
            We're building the clearest way to manage business spending
          </h1>
          <p className="mt-4 text-muted-foreground">
            Spend.In started with a simple frustration: tracking business expenses shouldn't
            require a finance degree or a stack of spreadsheets. Today, thousands of businesses
            use Spend.In to keep their spending organized, approved, and easy to understand.
          </p>
        </div>
      </section>

      <section className="bg-section py-16">
        <div className="mx-auto grid max-w-5xl grid-cols-2 gap-8 px-4 sm:px-6 lg:grid-cols-4">
          {stats.map((stat) => (
            <div key={stat.label} className="text-center">
              <p className="text-3xl font-bold text-foreground">{stat.value}</p>
              <p className="mt-1 text-sm text-muted-foreground">{stat.label}</p>
            </div>
          ))}
        </div>
      </section>

      <section className="py-20">
        <div className="mx-auto max-w-6xl px-4 sm:px-6">
          <div className="mx-auto max-w-xl text-center">
            <h2 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
              What we care about
            </h2>
          </div>
          <div className="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2">
            {values.map((value) => (
              <div key={value.title} className="rounded-2xl border border-border bg-background p-6">
                <span className="flex h-10 w-10 items-center justify-center rounded-lg bg-accent-soft text-accent">
                  <value.icon className="h-5 w-5" />
                </span>
                <h3 className="mt-4 font-semibold text-foreground">{value.title}</h3>
                <p className="mt-2 text-sm text-muted-foreground">{value.description}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section className="bg-dark py-20">
        <div className="mx-auto max-w-3xl px-4 text-center sm:px-6">
          <h2 className="text-3xl font-bold tracking-tight text-white sm:text-4xl">
            Want to see it in action?
          </h2>
          <p className="mt-3 text-white/60">
            Get a free demo and see how Spend.In can simplify your business spending.
          </p>
          <Link
            to="/contact"
            className="mt-6 inline-block rounded-full bg-accent px-7 py-3 text-sm font-semibold text-white hover:bg-accent/90"
          >
            Get a Free Demo
          </Link>
        </div>
      </section>
    </>
  );
}
