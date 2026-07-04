export type Post = {
  slug: string;
  title: string;
  excerpt: string;
  date: string;
  readTime: string;
  category: string;
  author: string;
  body: string[];
};

export const posts: Post[] = [
  {
    slug: "5-ways-to-cut-business-expenses",
    title: "5 Ways to Cut Business Expenses Without Cutting Corners",
    excerpt:
      "Trimming costs doesn't have to mean sacrificing quality. Here are five practical ways growing businesses can reduce spend this quarter.",
    date: "2026-05-12",
    readTime: "6 min read",
    category: "Finance Tips",
    author: "Alina Cooper",
    body: [
      "Every business hits a point where spending needs a second look. The good news is that cutting costs doesn't require cutting corners — it just requires visibility into where the money is actually going.",
      "Start by auditing recurring subscriptions. It's common for teams to accumulate software licenses that overlap in function or simply go unused after the initial rollout. A quarterly audit alone can uncover 10-15% in reclaimable spend.",
      "Next, consolidate vendors where it makes sense. Paying three vendors for adjacent services often costs more in overhead — invoicing, support, integration — than a single vendor with a broader offering.",
      "Automate expense approvals so small purchases don't require manager time to process. The administrative cost of manual approval workflows adds up fast across a growing team.",
      "Finally, review your expense categories monthly, not annually. Trends are far easier to correct when you catch them within a few weeks instead of finding them in a year-end report.",
    ],
  },
  {
    slug: "how-to-build-an-expense-policy",
    title: "How to Build an Expense Policy Your Team Will Actually Follow",
    excerpt:
      "A good expense policy is short, clear, and easy to check against. Here's how to write one that people don't route around.",
    date: "2026-04-02",
    readTime: "5 min read",
    category: "Operations",
    author: "Jimmy Bartney",
    body: [
      "Most expense policies fail for the same reason: nobody reads them. If your policy is a ten-page PDF buried in a shared drive, it's not a policy — it's a formality.",
      "Keep it to a single page. Cover what's reimbursable, what needs pre-approval, and what the submission deadline is. Anything more detailed belongs in an FAQ, not the core policy.",
      "Set clear spending limits per category and communicate them in the tool people already use to submit expenses, not in a separate document they have to go find.",
      "Review the policy every two quarters. Teams change, vendors change, and a policy that made sense a year ago can quietly become a source of friction.",
    ],
  },
  {
    slug: "spend-in-2026-roadmap",
    title: "What's Next for Spend.In: Our 2026 Roadmap",
    excerpt:
      "A look at what we're building this year, from deeper reporting to smarter approval automation.",
    date: "2026-02-18",
    readTime: "4 min read",
    category: "Product",
    author: "Moritika Kazuki",
    body: [
      "2026 is shaping up to be our biggest year yet. Based on feedback from hundreds of finance teams, we're focusing on three areas: reporting, automation, and integrations.",
      "On reporting, we're shipping customizable dashboards so finance leads can build the exact view they need without waiting on an export.",
      "On automation, approval routing will get smarter — requests will be routed based on amount, category, and requester history, cutting manual review time significantly.",
      "And on integrations, we're expanding beyond accounting software to connect directly with the banking and card platforms our customers already use.",
    ],
  },
];
