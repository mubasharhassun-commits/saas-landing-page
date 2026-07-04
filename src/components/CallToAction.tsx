const bars = [45, 70, 55, 85, 60];

export function CallToAction() {
  return (
    <section className="relative overflow-hidden bg-dark py-20">
      <div className="pointer-events-none absolute -top-40 -left-40 -z-10 h-96 w-96 rounded-full bg-accent/20 blur-3xl" />

      <div className="mx-auto grid max-w-6xl grid-cols-1 items-center gap-12 px-4 sm:px-6 lg:grid-cols-2">
        <div>
          <p className="text-xs font-semibold tracking-wide text-accent uppercase">Download Now!</p>
          <h2 className="mt-3 text-3xl font-bold tracking-tight text-white sm:text-4xl">
            Start Track Your Business Expenses Today
          </h2>
          <p className="mt-4 text-white/60">
            Are you ready to make your business more organized? Download Spend.In now!
          </p>
          <a
            href="#"
            className="mt-8 inline-block rounded-full bg-accent px-7 py-3 text-sm font-semibold text-white hover:bg-accent/90"
          >
            Get a Free Demo
          </a>
        </div>

        <div className="rounded-2xl bg-white p-6 shadow-xl">
          <p className="text-sm font-semibold text-foreground">Spending Statistics</p>
          <div className="mt-5 flex flex-col gap-6 sm:flex-row sm:items-center">
            <div
              className="relative flex h-36 w-36 shrink-0 items-center justify-center rounded-full"
              style={{
                background:
                  "conic-gradient(var(--accent) 0% 55%, #f472b6 55% 78%, #60a5fa 78% 100%)",
              }}
            >
              <div className="flex h-24 w-24 flex-col items-center justify-center rounded-full bg-white text-center">
                <p className="text-[10px] text-muted-foreground">Overall Spending</p>
                <p className="text-sm font-bold text-foreground">$19,760.00</p>
              </div>
            </div>

            <div className="flex flex-1 items-end gap-2">
              {bars.map((h, i) => (
                <div key={i} className="flex-1 rounded-t-sm bg-accent" style={{ height: `${h}px` }} />
              ))}
            </div>
          </div>
          <div className="mt-5 flex items-center justify-between border-t border-border pt-4 text-xs">
            <span className="flex items-center gap-1.5 text-muted-foreground">
              <span className="h-2 w-2 rounded-full bg-pink-400" /> Employees Salary
            </span>
            <span className="font-semibold text-foreground">$8,000.00</span>
          </div>
        </div>
      </div>
    </section>
  );
}
