import { Receipt, ShieldCheck, LineChart, Check } from "lucide-react";

const features = [
  {
    icon: Receipt,
    title: "Automated Expense Tracking",
    description:
      "Every transaction is captured and categorized the moment it happens — no manual entry required.",
  },
  {
    icon: ShieldCheck,
    title: "Instant Approvals",
    description:
      "Set spending limits and approve requests in seconds, right from your phone or desktop.",
  },
  {
    icon: LineChart,
    title: "Real-Time Reports",
    description:
      "See exactly where your money goes with live dashboards built for founders and finance teams.",
  },
];

const transactions = [
  { name: "Adobe Creative Cloud", amount: "-$52.00" },
  { name: "AWS Hosting", amount: "-$318.40" },
  { name: "Office Supplies", amount: "-$76.20" },
];

const approvals = ["Team travel budget", "Marketing spend", "Software renewals"];

const reportBars = [30, 55, 42, 68, 50, 74];

function CardVisual({ index }: { index: number }) {
  if (index === 0) {
    return (
      <div className="flex h-full flex-col justify-center gap-3 p-6">
        {transactions.map((t) => (
          <div
            key={t.name}
            className="flex items-center justify-between rounded-lg bg-background px-3 py-2.5 text-xs"
          >
            <span className="font-medium text-foreground">{t.name}</span>
            <span className="text-muted-foreground">{t.amount}</span>
          </div>
        ))}
      </div>
    );
  }
  if (index === 1) {
    return (
      <div className="flex h-full flex-col justify-center gap-3 p-6">
        {approvals.map((a) => (
          <div
            key={a}
            className="flex items-center gap-2.5 rounded-lg bg-background px-3 py-2.5 text-xs font-medium text-foreground"
          >
            <span className="flex h-4 w-4 items-center justify-center rounded-full bg-accent text-white">
              <Check className="h-2.5 w-2.5" />
            </span>
            {a}
          </div>
        ))}
      </div>
    );
  }
  return (
    <div className="flex h-full items-end gap-2 p-6">
      {reportBars.map((h, i) => (
        <div
          key={i}
          className="flex-1 rounded-t-sm bg-accent"
          style={{ height: `${h}%`, opacity: 0.5 + i * 0.08 }}
        />
      ))}
    </div>
  );
}

export function Features() {
  return (
    <section id="benefit" className="py-20">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <div className="grid gap-8 md:grid-cols-2 md:items-end">
          <div>
            <p className="text-xs font-semibold tracking-wide text-accent uppercase">
              Why Use Spend.In
            </p>
            <h2 className="mt-3 text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
              Easy, Simple, Affordable
            </h2>
          </div>
          <p className="text-muted-foreground">
            We built Spend.In to remove the friction from business spending — no
            spreadsheets, no guesswork, just clear visibility from purchase to
            reconciliation.
          </p>
        </div>

        <div className="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {features.map((feature, i) => (
            <div key={feature.title} className="rounded-2xl border border-border bg-background">
              <div className="aspect-square overflow-hidden rounded-t-2xl bg-section">
                <CardVisual index={i} />
              </div>
              <div className="p-6">
                <span className="flex h-10 w-10 items-center justify-center rounded-lg bg-accent-soft text-accent">
                  <feature.icon className="h-5 w-5" />
                </span>
                <h3 className="mt-4 font-semibold text-foreground">{feature.title}</h3>
                <p className="mt-2 text-sm text-muted-foreground">{feature.description}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}
