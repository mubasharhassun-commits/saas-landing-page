import { useState } from "react";
import { Check, X, Sparkles, Crown, Zap } from "lucide-react";
import { cn } from "@/lib/utils";

const plans = [
  {
    name: "Free",
    icon: Sparkles,
    tagline: "Perfect plan to get started",
    description: "A free plan grants you access to some cool features of Spend.In.",
    price: 0,
    cta: "Get Your Free Plan",
    popular: false,
    features: [
      { label: "Sync accross device", included: true },
      { label: "5 workspace", included: true },
      { label: "Collaborate with 5 user", included: true },
      { label: "Sharing permission", included: false },
      { label: "Admin tools", included: false },
      { label: "100+ integrations", included: false },
    ],
  },
  {
    name: "Pro",
    icon: Crown,
    tagline: "Perfect plan for professionals!",
    description: "For professional only! Start arranging your expenses with our best templates.",
    price: 12,
    cta: "Get Started",
    popular: true,
    features: [
      { label: "Everything in Free Plan", included: true },
      { label: "Unlimited workspace", included: true },
      { label: "Collaborative workspace", included: true },
      { label: "Sharing permission", included: true },
      { label: "Admin tools", included: true },
      { label: "100+ integrations", included: true },
    ],
  },
  {
    name: "Ultimate",
    icon: Zap,
    tagline: "Best suits for great company!",
    description: "If you a finance manager at big company, this plan is a perfect match.",
    price: 33,
    cta: "Get Started",
    popular: false,
    features: [
      { label: "Everything in Pro Plan", included: true },
      { label: "Daily performance reports", included: true },
      { label: "Dedicated assistant", included: true },
      { label: "Artificial intelligence", included: true },
      { label: "Marketing tools & automations", included: true },
      { label: "Advanced security", included: true },
    ],
  },
];

export function Pricing() {
  const [yearly, setYearly] = useState(false);

  return (
    <section id="pricing" className="bg-section py-20">
      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <div className="mx-auto max-w-xl text-center">
          <h2 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
            Ready to Get Started?
          </h2>
          <p className="mt-3 text-muted-foreground">Choose a plan that suits your business needs</p>
        </div>

        <div className="mt-8 flex flex-col items-center gap-2">
          <div className="flex items-center gap-3">
            <span className={cn("text-sm font-semibold", !yearly ? "text-foreground" : "text-muted-foreground")}>
              Monthly
            </span>
            <button
              onClick={() => setYearly((v) => !v)}
              aria-label="Toggle yearly billing"
              className="relative h-7 w-13 rounded-full bg-accent transition-colors"
            >
              <span
                className={cn(
                  "absolute top-1 h-5 w-5 rounded-full bg-white transition-all",
                  yearly ? "left-7" : "left-1",
                )}
              />
            </button>
            <span className={cn("text-sm font-semibold", yearly ? "text-foreground" : "text-muted-foreground")}>
              Yearly
            </span>
          </div>
          <span className="rounded-full bg-accent-soft px-3 py-1 text-xs font-semibold text-accent">
            Save 65%
          </span>
        </div>

        <div className="mt-10 grid grid-cols-1 gap-6 lg:grid-cols-3">
          {plans.map((plan) => {
            const price = plan.price === 0 ? 0 : Math.round(yearly ? plan.price * 0.35 : plan.price);
            return (
              <div
                key={plan.name}
                className={cn(
                  "relative flex flex-col rounded-2xl border bg-background p-8",
                  plan.popular ? "border-accent shadow-lg" : "border-border",
                )}
              >
                {plan.popular && (
                  <span className="absolute top-8 right-8 rounded-full bg-foreground px-3 py-1 text-xs font-semibold text-white">
                    Popular
                  </span>
                )}
                <div className="flex items-center gap-2">
                  <plan.icon className="h-6 w-6 text-accent" />
                  <h3 className="text-lg font-bold text-foreground">{plan.name}</h3>
                </div>
                <p className="mt-2 text-sm text-muted-foreground">{plan.tagline}</p>

                <p className="mt-5">
                  <span className="text-4xl font-bold text-foreground">${price}</span>
                  <span className="text-sm text-muted-foreground">/month</span>
                </p>

                <p className="mt-4 text-sm text-muted-foreground">{plan.description}</p>

                <ul className="mt-6 flex-1 space-y-3">
                  {plan.features.map((f) => (
                    <li key={f.label} className="flex items-center gap-2.5 text-sm">
                      <span
                        className={cn(
                          "flex h-5 w-5 shrink-0 items-center justify-center rounded-full",
                          f.included ? "bg-green-500 text-white" : "bg-section text-muted-foreground",
                        )}
                      >
                        {f.included ? <Check className="h-3 w-3" /> : <X className="h-3 w-3" />}
                      </span>
                      <span className={f.included ? "text-foreground" : "text-muted-foreground"}>
                        {f.label}
                      </span>
                    </li>
                  ))}
                </ul>

                <a
                  href="#"
                  className="mt-8 rounded-full bg-accent px-5 py-3 text-center text-sm font-semibold text-white hover:bg-accent/90"
                >
                  {plan.cta}
                </a>
              </div>
            );
          })}
        </div>
      </div>
    </section>
  );
}
