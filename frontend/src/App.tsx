import { Link } from 'react-router-dom';

function App() {
  return (
    <main className="min-h-screen bg-slate-950 text-slate-100">
      <section className="mx-auto flex min-h-screen max-w-6xl flex-col justify-center px-6 py-20">
        <span className="mb-4 inline-flex w-fit rounded-full border border-slate-800 bg-slate-900 px-3 py-1 text-sm text-slate-300">
          AI-powered job search and applications
        </span>
        <h1 className="max-w-3xl text-4xl font-semibold tracking-tight sm:text-6xl">
          HirePilot is your intelligent career operating system.
        </h1>
        <p className="mt-6 max-w-2xl text-lg text-slate-400">
          Search jobs, tailor your resume, generate cover letters, and manage every application from a single platform.
        </p>
        <div className="mt-10 flex flex-wrap gap-4">
          <Link
            to="/jobs"
            className="rounded-lg bg-cyan-500 px-5 py-3 font-medium text-slate-950 transition hover:bg-cyan-400"
          >
            Explore jobs
          </Link>
          <Link
            to="/resume"
            className="rounded-lg border border-slate-700 px-5 py-3 font-medium text-slate-200 transition hover:border-slate-500"
          >
            Manage resumes
          </Link>
        </div>
      </section>
    </main>
  );
}

export default App;
