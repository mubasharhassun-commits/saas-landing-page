import { useState, type FormEvent } from "react";
import { Mail, Phone, MapPin } from "lucide-react";
import { Button } from "@/components/ui/button";

const info = [
  { icon: Mail, label: "Email", value: "hello@spendin.app" },
  { icon: Phone, label: "Phone", value: "+1 (415) 555-0132" },
  { icon: MapPin, label: "Office", value: "San Francisco, CA" },
];

export function Contact() {
  const [submitted, setSubmitted] = useState(false);

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();
    setSubmitted(true);
  };

  return (
    <section className="py-20">
      <div className="mx-auto max-w-5xl px-4 sm:px-6">
        <div className="mx-auto max-w-xl text-center">
          <p className="text-xs font-semibold tracking-wide text-accent uppercase">Contact Us</p>
          <h1 className="mt-3 text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
            We'd love to hear from you
          </h1>
          <p className="mt-3 text-muted-foreground">
            Questions about Spend.In, pricing, or a demo? Send us a message and we'll get back to
            you within one business day.
          </p>
        </div>

        <div className="mt-14 grid grid-cols-1 gap-10 lg:grid-cols-[1fr_1.3fr]">
          <div className="space-y-6">
            {info.map((item) => (
              <div key={item.label} className="flex items-start gap-4 rounded-2xl border border-border bg-background p-5">
                <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-accent-soft text-accent">
                  <item.icon className="h-5 w-5" />
                </span>
                <div>
                  <p className="text-sm text-muted-foreground">{item.label}</p>
                  <p className="font-semibold text-foreground">{item.value}</p>
                </div>
              </div>
            ))}
          </div>

          <div className="rounded-2xl border border-border bg-background p-6 sm:p-8">
            {submitted ? (
              <div className="flex h-full flex-col items-center justify-center py-12 text-center">
                <h3 className="text-xl font-semibold text-foreground">Message sent!</h3>
                <p className="mt-2 text-muted-foreground">
                  Thanks for reaching out — our team will get back to you within one business day.
                </p>
              </div>
            ) : (
              <form onSubmit={handleSubmit} className="space-y-5">
                <div className="grid grid-cols-1 gap-5 sm:grid-cols-2">
                  <div>
                    <label className="text-sm font-medium text-foreground" htmlFor="name">
                      Full name
                    </label>
                    <input
                      id="name"
                      required
                      type="text"
                      placeholder="Jane Doe"
                      className="mt-2 w-full rounded-lg border border-border px-4 py-2.5 text-sm outline-none focus:border-accent"
                    />
                  </div>
                  <div>
                    <label className="text-sm font-medium text-foreground" htmlFor="email">
                      Email
                    </label>
                    <input
                      id="email"
                      required
                      type="email"
                      placeholder="jane@company.com"
                      className="mt-2 w-full rounded-lg border border-border px-4 py-2.5 text-sm outline-none focus:border-accent"
                    />
                  </div>
                </div>

                <div>
                  <label className="text-sm font-medium text-foreground" htmlFor="subject">
                    Subject
                  </label>
                  <input
                    id="subject"
                    required
                    type="text"
                    placeholder="How can we help?"
                    className="mt-2 w-full rounded-lg border border-border px-4 py-2.5 text-sm outline-none focus:border-accent"
                  />
                </div>

                <div>
                  <label className="text-sm font-medium text-foreground" htmlFor="message">
                    Message
                  </label>
                  <textarea
                    id="message"
                    required
                    rows={5}
                    placeholder="Tell us a bit about what you need..."
                    className="mt-2 w-full resize-none rounded-lg border border-border px-4 py-2.5 text-sm outline-none focus:border-accent"
                  />
                </div>

                <Button type="submit" size="lg" className="w-full rounded-full">
                  Send Message
                </Button>
              </form>
            )}
          </div>
        </div>
      </div>
    </section>
  );
}
