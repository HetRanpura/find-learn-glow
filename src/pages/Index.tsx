import { useState } from "react";
import { Search, MapPin, BookOpen, Star, ArrowRight, GraduationCap, Clock, Shield } from "lucide-react";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Link } from "react-router-dom";
import { motion } from "framer-motion";

const subjects = ["Mathematics", "Physics", "Chemistry", "English", "Biology", "Computer Science", "Hindi", "Economics"];

const featuredTutors = [
  { id: 1, name: "Dr. Ananya Sharma", subject: "Mathematics", rating: 4.9, reviews: 128, price: 800, location: "Delhi", experience: "8 yrs", avatar: "AS" },
  { id: 2, name: "Rajesh Kumar", subject: "Physics", rating: 4.8, reviews: 95, price: 700, location: "Mumbai", experience: "6 yrs", avatar: "RK" },
  { id: 3, name: "Priya Menon", subject: "Chemistry", rating: 4.7, reviews: 73, price: 750, location: "Bangalore", experience: "5 yrs", avatar: "PM" },
  { id: 4, name: "Vikram Singh", subject: "English", rating: 4.9, reviews: 156, price: 650, location: "Pune", experience: "10 yrs", avatar: "VS" },
  { id: 5, name: "Neha Gupta", subject: "Biology", rating: 4.6, reviews: 62, price: 700, location: "Hyderabad", experience: "4 yrs", avatar: "NG" },
  { id: 6, name: "Arjun Patel", subject: "Computer Science", rating: 4.8, reviews: 88, price: 900, location: "Chennai", experience: "7 yrs", avatar: "AP" },
];

const Index = () => {
  const [searchQuery, setSearchQuery] = useState("");
  const [selectedSubject, setSelectedSubject] = useState<string | null>(null);

  return (
    <div className="min-h-screen bg-background">
      {/* Navbar */}
      <nav className="fixed top-0 w-full z-50 glass">
        <div className="container mx-auto flex items-center justify-between py-4 px-6">
          <Link to="/" className="flex items-center gap-2">
            <GraduationCap className="w-8 h-8 text-primary" />
            <span className="text-xl font-bold text-foreground">TutorFind</span>
          </Link>
          <div className="hidden md:flex items-center gap-6">
            <Link to="/" className="text-sm text-foreground hover:text-primary transition-colors">Home</Link>
            <Link to="/signup" className="text-sm text-muted-foreground hover:text-primary transition-colors">Sign Up</Link>
            <Link to="/login">
              <Button size="sm" className="bg-primary text-primary-foreground hover:bg-lime-glow">
                Login
              </Button>
            </Link>
          </div>
        </div>
      </nav>

      {/* Hero */}
      <section className="relative pt-32 pb-20 overflow-hidden" style={{ background: "var(--gradient-hero)" }}>
        <div className="absolute inset-0 overflow-hidden">
          <div className="absolute top-20 left-1/4 w-96 h-96 rounded-full bg-primary/5 blur-3xl animate-pulse-glow" />
          <div className="absolute bottom-10 right-1/4 w-72 h-72 rounded-full bg-secondary/5 blur-3xl animate-pulse-glow" style={{ animationDelay: "1.5s" }} />
        </div>

        <div className="container mx-auto px-6 relative z-10">
          <motion.div
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.7 }}
            className="max-w-3xl mx-auto text-center"
          >
            <h1 className="text-5xl md:text-6xl font-bold mb-6 leading-tight">
              Find Your Perfect{" "}
              <span className="text-gradient">Home Tutor</span>
            </h1>
            <p className="text-lg text-muted-foreground mb-10 max-w-xl mx-auto">
              Connect with expert tutors in your area. Personalized learning, flexible scheduling, verified profiles.
            </p>

            {/* Search Bar */}
            <div className="glass rounded-2xl p-2 flex flex-col sm:flex-row gap-2 max-w-2xl mx-auto glow-lime">
              <div className="relative flex-1">
                <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground" />
                <Input
                  placeholder="Search by subject, tutor name..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="pl-12 h-12 bg-muted/50 border-none text-foreground placeholder:text-muted-foreground focus-visible:ring-primary"
                />
              </div>
              <div className="relative flex-1">
                <MapPin className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground" />
                <Input
                  placeholder="Your location..."
                  className="pl-12 h-12 bg-muted/50 border-none text-foreground placeholder:text-muted-foreground focus-visible:ring-primary"
                />
              </div>
              <Button className="h-12 px-8 bg-primary text-primary-foreground hover:bg-lime-glow font-semibold">
                Search
              </Button>
            </div>
          </motion.div>
        </div>
      </section>

      {/* Subject Pills */}
      <section className="py-12 bg-background">
        <div className="container mx-auto px-6">
          <div className="flex flex-wrap justify-center gap-3">
            {subjects.map((s) => (
              <button
                key={s}
                onClick={() => setSelectedSubject(selectedSubject === s ? null : s)}
                className={`px-5 py-2.5 rounded-full text-sm font-medium transition-all ${
                  selectedSubject === s
                    ? "bg-primary text-primary-foreground glow-lime"
                    : "bg-muted text-muted-foreground hover:bg-muted/80 hover:text-foreground"
                }`}
              >
                {s}
              </button>
            ))}
          </div>
        </div>
      </section>

      {/* Stats */}
      <section className="py-8 bg-background">
        <div className="container mx-auto px-6">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
            {[
              { icon: GraduationCap, label: "Verified Tutors", value: "2,500+" },
              { icon: BookOpen, label: "Subjects", value: "50+" },
              { icon: Star, label: "Avg Rating", value: "4.8" },
              { icon: Shield, label: "Background Checked", value: "100%" },
            ].map((stat) => (
              <div key={stat.label} className="glass rounded-xl p-6 text-center">
                <stat.icon className="w-8 h-8 text-secondary mx-auto mb-3" />
                <div className="text-2xl font-bold text-foreground">{stat.value}</div>
                <div className="text-sm text-muted-foreground">{stat.label}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Featured Tutors */}
      <section className="py-16 bg-background">
        <div className="container mx-auto px-6">
          <div className="flex items-center justify-between mb-10">
            <h2 className="text-3xl font-bold text-foreground">Featured Tutors</h2>
            <Button variant="ghost" className="text-primary hover:text-lime-glow">
              View All <ArrowRight className="ml-2 w-4 h-4" />
            </Button>
          </div>

          <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            {featuredTutors.map((tutor, i) => (
              <motion.div
                key={tutor.id}
                initial={{ opacity: 0, y: 20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: i * 0.1 }}
              >
                <Link to={`/tutor/${tutor.id}`}>
                  <div className="glass rounded-xl p-6 hover:border-primary/30 transition-all group cursor-pointer">
                    <div className="flex items-start gap-4 mb-4">
                      <div className="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold text-lg">
                        {tutor.avatar}
                      </div>
                      <div className="flex-1">
                        <h3 className="font-semibold text-foreground group-hover:text-primary transition-colors">{tutor.name}</h3>
                        <p className="text-sm text-secondary">{tutor.subject}</p>
                      </div>
                    </div>
                    <div className="flex items-center gap-4 text-sm text-muted-foreground mb-4">
                      <span className="flex items-center gap-1">
                        <Star className="w-4 h-4 text-primary fill-primary" /> {tutor.rating} ({tutor.reviews})
                      </span>
                      <span className="flex items-center gap-1">
                        <Clock className="w-4 h-4" /> {tutor.experience}
                      </span>
                      <span className="flex items-center gap-1">
                        <MapPin className="w-4 h-4" /> {tutor.location}
                      </span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span className="text-lg font-bold text-foreground">₹{tutor.price}<span className="text-sm text-muted-foreground font-normal">/hr</span></span>
                      <Button size="sm" className="bg-primary text-primary-foreground hover:bg-lime-glow">
                        Book Now
                      </Button>
                    </div>
                  </div>
                </Link>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="py-12 border-t border-border bg-card/30">
        <div className="container mx-auto px-6 text-center text-muted-foreground text-sm">
          <div className="flex items-center justify-center gap-2 mb-4">
            <GraduationCap className="w-5 h-5 text-primary" />
            <span className="font-semibold text-foreground">TutorFind</span>
          </div>
          <p>© 2026 TutorFind. All rights reserved.</p>
        </div>
      </footer>
    </div>
  );
};

export default Index;
