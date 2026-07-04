import { Link } from "react-router-dom";
import { Pricing } from "@/components/Pricing";
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from "@/components/ui/accordion";
import { faqs } from "@/data/faqs";

export function PricingPage() {
  return (
    <>
      <Pricing />

      <section className="py-20">
        <div className="mx-auto max-w-3xl px-4 sm:px-6">
          <div className="text-center">
            <p className="text-xs font-semibold tracking-wide text-accent uppercase">Questions</p>
            <h2 className="mt-3 text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
              Pricing FAQ
            </h2>
          </div>

          <div className="mt-10 rounded-2xl border border-border bg-background p-6 sm:p-8">
            <Accordion type="single" collapsible>
              {faqs.slice(0, 4).map((faq) => (
                <AccordionItem key={faq.question} value={faq.question}>
                  <AccordionTrigger className="text-base text-foreground">
                    {faq.question}
                  </AccordionTrigger>
                  <AccordionContent className="text-muted-foreground">{faq.answer}</AccordionContent>
                </AccordionItem>
              ))}
            </Accordion>
          </div>

          <div className="mt-6 text-center">
            <Link to="/faq" className="text-sm font-semibold text-accent hover:underline">
              See all FAQs →
            </Link>
          </div>
        </div>
      </section>
    </>
  );
}
