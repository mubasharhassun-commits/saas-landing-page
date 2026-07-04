import { useRef } from "react";
import { ArrowLeft, ArrowRight, User } from "lucide-react";

const testimonials = [
  {
    title: "It's just incredible!",
    quote:
      "It's just 1 month since I'm using Spend.In to manage my business expenses, but the result is very satisfying! My business finance now more neat than before, thanks to Spend.In!",
    name: "Jimmy Bartney",
    role: "Product Manager at Picko Lab",
  },
  {
    title: "Satisfied User Here!",
    quote:
      "Never thought that with Spend.In managing my business expenses is so easy! Been using this platform for 3 months and still counting!",
    name: "Alina Cooper",
    role: "Head of Finance at Nova Retail",
  },
  {
    title: "No doubt, Spend.In is the best!",
    quote:
      "“The best”! That's what I want to say to this platform, didn't know that there's a platform to help you manage your business expenses like this! Very recommended to you who have a big business!",
    name: "Moritika Kazuki",
    role: "Finance Manager at Mangan",
  },
];

export function Testimonials() {
  const scrollerRef = useRef<HTMLDivElement>(null);

  const scroll = (direction: -1 | 1) => {
    scrollerRef.current?.scrollBy({ left: direction * 340, behavior: "smooth" });
  };

  return (
    <section className="relative overflow-hidden bg-dark py-20">
      <div className="pointer-events-none absolute inset-0 -z-10">
        <div className="absolute -top-32 -left-32 h-96 w-96 rounded-full bg-accent/20 blur-3xl" />
        <div className="absolute top-40 -right-24 h-96 w-96 rounded-full bg-accent/10 blur-3xl" />
      </div>

      <div className="mx-auto max-w-6xl px-4 sm:px-6">
        <div className="mx-auto max-w-xl text-center">
          <p className="text-xs font-semibold tracking-wide text-accent uppercase">What They Say</p>
          <h2 className="mt-3 text-3xl font-bold tracking-tight text-white sm:text-4xl">
            Our User Kind Words
          </h2>
          <p className="mt-3 text-white/60">
            Here are some testimonials from our user after using Spend.In to manage their
            business expenses.
          </p>
        </div>

        <div
          ref={scrollerRef}
          className="mt-12 flex snap-x snap-mandatory gap-6 overflow-x-auto pb-2 [scrollbar-width:none] lg:grid lg:grid-cols-3 lg:overflow-visible"
        >
          {testimonials.map((t) => (
            <div
              key={t.name}
              className="w-[320px] shrink-0 snap-start rounded-2xl border border-white/10 bg-white/5 p-6 lg:w-auto"
            >
              <h3 className="font-semibold text-white">{t.title}</h3>
              <p className="mt-3 text-sm text-white/60">{t.quote}</p>
              <div className="mt-6 flex items-center gap-3 border-t border-white/10 pt-5">
                <span className="flex h-11 w-11 items-center justify-center rounded-full bg-accent-soft/20 text-accent">
                  <User className="h-5 w-5" />
                </span>
                <div>
                  <p className="text-sm font-semibold text-white">{t.name}</p>
                  <p className="text-xs text-white/50">{t.role}</p>
                </div>
              </div>
            </div>
          ))}
        </div>

        <div className="mt-8 flex items-center justify-center gap-3">
          <button
            onClick={() => scroll(-1)}
            aria-label="Previous testimonial"
            className="flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/15 lg:hidden"
          >
            <ArrowLeft className="h-4 w-4" />
          </button>
          <button
            onClick={() => scroll(1)}
            aria-label="Next testimonial"
            className="flex h-11 w-11 items-center justify-center rounded-full bg-accent text-white hover:bg-accent/90 lg:hidden"
          >
            <ArrowRight className="h-4 w-4" />
          </button>
        </div>
      </div>
    </section>
  );
}
