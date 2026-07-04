import { Link } from "react-router-dom";
import { ArrowRight } from "lucide-react";
import { posts } from "@/data/posts";

const formatDate = (iso: string) =>
  new Date(iso).toLocaleDateString("en-US", { month: "long", day: "numeric", year: "numeric" });

export function BlogIndex() {
  return (
    <section className="py-20">
      <div className="mx-auto max-w-5xl px-4 sm:px-6">
        <div className="mx-auto max-w-xl text-center">
          <p className="text-xs font-semibold tracking-wide text-accent uppercase">Blog</p>
          <h1 className="mt-3 text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
            Insights on business spending
          </h1>
          <p className="mt-3 text-muted-foreground">
            Tips, product updates, and ideas to help you manage business expenses better.
          </p>
        </div>

        <div className="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {posts.map((post) => (
            <Link
              key={post.slug}
              to={`/blog/${post.slug}`}
              className="group flex flex-col rounded-2xl border border-border bg-background p-6 hover:border-accent"
            >
              <span className="w-fit rounded-full bg-accent-soft px-3 py-1 text-xs font-semibold text-accent">
                {post.category}
              </span>
              <h2 className="mt-4 font-semibold text-foreground group-hover:text-accent">
                {post.title}
              </h2>
              <p className="mt-2 flex-1 text-sm text-muted-foreground">{post.excerpt}</p>
              <div className="mt-5 flex items-center justify-between border-t border-border pt-4 text-xs text-muted-foreground">
                <span>
                  {formatDate(post.date)} · {post.readTime}
                </span>
                <ArrowRight className="h-4 w-4 text-accent opacity-0 transition-opacity group-hover:opacity-100" />
              </div>
            </Link>
          ))}
        </div>
      </div>
    </section>
  );
}
