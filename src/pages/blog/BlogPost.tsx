import { Link, Navigate, useParams } from "react-router-dom";
import { ArrowLeft, User } from "lucide-react";
import { posts } from "@/data/posts";

const formatDate = (iso: string) =>
  new Date(iso).toLocaleDateString("en-US", { month: "long", day: "numeric", year: "numeric" });

export function BlogPost() {
  const { slug } = useParams();
  const post = posts.find((p) => p.slug === slug);

  if (!post) {
    return <Navigate to="/blog" replace />;
  }

  return (
    <article className="py-20">
      <div className="mx-auto max-w-2xl px-4 sm:px-6">
        <Link to="/blog" className="flex items-center gap-1.5 text-sm font-semibold text-accent hover:underline">
          <ArrowLeft className="h-4 w-4" /> Back to Blog
        </Link>

        <span className="mt-6 inline-block w-fit rounded-full bg-accent-soft px-3 py-1 text-xs font-semibold text-accent">
          {post.category}
        </span>
        <h1 className="mt-4 text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
          {post.title}
        </h1>

        <div className="mt-5 flex items-center gap-3 border-b border-border pb-6">
          <span className="flex h-9 w-9 items-center justify-center rounded-full bg-section text-muted-foreground">
            <User className="h-4 w-4" />
          </span>
          <div className="text-sm">
            <p className="font-semibold text-foreground">{post.author}</p>
            <p className="text-muted-foreground">
              {formatDate(post.date)} · {post.readTime}
            </p>
          </div>
        </div>

        <div className="mt-8 space-y-5 text-muted-foreground">
          {post.body.map((paragraph, i) => (
            <p key={i}>{paragraph}</p>
          ))}
        </div>
      </div>
    </article>
  );
}
