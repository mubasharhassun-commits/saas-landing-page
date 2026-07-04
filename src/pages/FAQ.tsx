import { Link } from "react-router-dom";
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from "@/components/ui/accordion";
import { faqs } from "@/data/faqs";

export function FAQ() {
  return (
    <section className="py-20">
      <div className="mx-auto max-w-3xl px-4 sm:px-6">
        <div className="text-center">
          <p className="text-xs font-semibold tracking-wide text-accent uppercase">Support</p>
          <h1 className="mt-3 text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
            Frequently Asked Questions
          </h1>
          <p className="mt-3 text-muted-foreground">
            Everything you need to know about Spend.In. Can't find the answer you're looking for?
          </p>
        </div>

        <div className="mt-12 rounded-2xl border border-border bg-background p-6 sm:p-8">
          <Accordion type="single" collapsible>
            {faqs.map((faq) => (
              <AccordionItem key={faq.question} value={faq.question}>
                <AccordionTrigger className="text-base text-foreground">
                  {faq.question}
                </AccordionTrigger>
                <AccordionContent className="text-muted-foreground">{faq.answer}</AccordionContent>
              </AccordionItem>
            ))}
          </Accordion>
        </div>

        <div className="mt-10 text-center">
          <p className="text-muted-foreground">Still have questions?</p>
          <Link
            to="/contact"
            className="mt-3 inline-block rounded-full bg-accent px-6 py-3 text-sm font-semibold text-white hover:bg-accent/90"
          >
            Contact Us
          </Link>
        </div>
      </div>
    </section>
  );
}
