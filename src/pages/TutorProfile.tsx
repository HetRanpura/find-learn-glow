import { useState } from "react";
import { Star, MapPin, Clock, BookOpen, Award, ArrowLeft, CheckCircle } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Calendar } from "@/components/ui/calendar";
import { Link } from "react-router-dom";
import { motion } from "framer-motion";
import { cn } from "@/lib/utils";
import { GraduationCap } from "lucide-react";
import CheckoutDialog from "@/components/CheckoutDialog";

const timeSlots = ["9:00 AM", "10:00 AM", "11:00 AM", "2:00 PM", "3:00 PM", "4:00 PM", "5:00 PM", "6:00 PM"];

const reviews = [
  { name: "Aarav M.", rating: 5, text: "Excellent tutor! My son's math grades improved dramatically.", date: "2 weeks ago" },
  { name: "Sneha R.", rating: 5, text: "Very patient and explains concepts clearly. Highly recommended!", date: "1 month ago" },
  { name: "Karthik P.", rating: 4, text: "Good teaching methodology. Flexible with timings.", date: "2 months ago" },
];

const TutorProfile = () => {
  const [selectedDate, setSelectedDate] = useState<Date | undefined>(undefined);
  const [selectedSlot, setSelectedSlot] = useState<string | null>(null);
  const [checkoutOpen, setCheckoutOpen] = useState(false);

  return (
    <div className="min-h-screen bg-background">
      {/* Navbar */}
      <nav className="fixed top-0 w-full z-50 glass">
        <div className="container mx-auto flex items-center justify-between py-4 px-6">
          <Link to="/" className="flex items-center gap-2">
            <GraduationCap className="w-8 h-8 text-primary" />
            <span className="text-xl font-bold text-foreground">TutorFind</span>
          </Link>
        </div>
      </nav>

      <div className="container mx-auto px-6 pt-28 pb-16">
        <Link to="/" className="inline-flex items-center gap-2 text-muted-foreground hover:text-primary transition-colors mb-8">
          <ArrowLeft className="w-4 h-4" /> Back to Search
        </Link>

        <div className="grid lg:grid-cols-3 gap-8">
          {/* Profile Info */}
          <motion.div
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            className="lg:col-span-2 space-y-6"
          >
            {/* Header Card */}
            <div className="glass rounded-2xl p-8">
              <div className="flex flex-col sm:flex-row items-start gap-6">
                <div className="w-24 h-24 rounded-2xl bg-primary/10 flex items-center justify-center text-primary font-bold text-3xl shrink-0">
                  AS
                </div>
                <div className="flex-1">
                  <div className="flex items-center gap-3 mb-2">
                    <h1 className="text-3xl font-bold text-foreground">Dr. Ananya Sharma</h1>
                    <CheckCircle className="w-6 h-6 text-primary" />
                  </div>
                  <p className="text-secondary text-lg mb-3">Mathematics Specialist</p>
                  <div className="flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                    <span className="flex items-center gap-1"><Star className="w-4 h-4 text-primary fill-primary" /> 4.9 (128 reviews)</span>
                    <span className="flex items-center gap-1"><Clock className="w-4 h-4" /> 8 years experience</span>
                    <span className="flex items-center gap-1"><MapPin className="w-4 h-4" /> Delhi, India</span>
                    <span className="flex items-center gap-1"><BookOpen className="w-4 h-4" /> 500+ sessions</span>
                  </div>
                </div>
                <div className="text-right">
                  <div className="text-3xl font-bold text-foreground">₹800</div>
                  <div className="text-sm text-muted-foreground">per hour</div>
                </div>
              </div>
            </div>

            {/* About */}
            <div className="glass rounded-2xl p-8">
              <h2 className="text-xl font-semibold text-foreground mb-4">About</h2>
              <p className="text-muted-foreground leading-relaxed">
                Ph.D. in Mathematics from IIT Delhi with 8 years of teaching experience. Specializing in Calculus, Algebra, and Competitive Math (JEE/Olympiad). I believe in building strong fundamentals and making math enjoyable through real-world applications and interactive problem-solving.
              </p>
              <div className="flex flex-wrap gap-2 mt-6">
                {["Calculus", "Algebra", "Trigonometry", "JEE Prep", "Olympiad Math", "Statistics"].map((tag) => (
                  <span key={tag} className="px-3 py-1.5 rounded-full text-xs font-medium bg-muted text-muted-foreground">
                    {tag}
                  </span>
                ))}
              </div>
            </div>

            {/* Qualifications */}
            <div className="glass rounded-2xl p-8">
              <h2 className="text-xl font-semibold text-foreground mb-4">Qualifications</h2>
              <div className="space-y-4">
                {[
                  { degree: "Ph.D. Mathematics", inst: "IIT Delhi", year: "2016" },
                  { degree: "M.Sc. Mathematics", inst: "Delhi University", year: "2012" },
                  { degree: "B.Sc. Mathematics (Hons)", inst: "St. Stephen's College", year: "2010" },
                ].map((q) => (
                  <div key={q.degree} className="flex items-center gap-4">
                    <Award className="w-5 h-5 text-secondary shrink-0" />
                    <div>
                      <div className="font-medium text-foreground">{q.degree}</div>
                      <div className="text-sm text-muted-foreground">{q.inst} • {q.year}</div>
                    </div>
                  </div>
                ))}
              </div>
            </div>

            {/* Reviews */}
            <div className="glass rounded-2xl p-8">
              <h2 className="text-xl font-semibold text-foreground mb-6">Reviews</h2>
              <div className="space-y-6">
                {reviews.map((r) => (
                  <div key={r.name} className="border-b border-border/50 pb-5 last:border-0 last:pb-0">
                    <div className="flex items-center justify-between mb-2">
                      <div className="flex items-center gap-2">
                        <div className="w-8 h-8 rounded-full bg-secondary/10 flex items-center justify-center text-secondary text-sm font-medium">
                          {r.name[0]}
                        </div>
                        <span className="font-medium text-foreground">{r.name}</span>
                      </div>
                      <span className="text-xs text-muted-foreground">{r.date}</span>
                    </div>
                    <div className="flex gap-0.5 mb-2">
                      {Array.from({ length: r.rating }).map((_, i) => (
                        <Star key={i} className="w-3.5 h-3.5 text-primary fill-primary" />
                      ))}
                    </div>
                    <p className="text-sm text-muted-foreground">{r.text}</p>
                  </div>
                ))}
              </div>
            </div>
          </motion.div>

          {/* Booking Sidebar */}
          <motion.div
            initial={{ opacity: 0, x: 20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ delay: 0.2 }}
          >
            <div className="glass rounded-2xl p-6 sticky top-28">
              <h2 className="text-xl font-semibold text-foreground mb-4">Book a Session</h2>

              <div className="mb-6">
                <label className="text-sm font-medium text-muted-foreground mb-2 block">Select Date</label>
                <Calendar
                  mode="single"
                  selected={selectedDate}
                  onSelect={setSelectedDate}
                  disabled={(date) => date < new Date() || date.getDay() === 0}
                  className={cn("p-3 pointer-events-auto rounded-xl bg-muted/30")}
                />
              </div>

              {selectedDate && (
                <motion.div
                  initial={{ opacity: 0, height: 0 }}
                  animate={{ opacity: 1, height: "auto" }}
                  className="mb-6"
                >
                  <label className="text-sm font-medium text-muted-foreground mb-3 block">Available Slots</label>
                  <div className="grid grid-cols-2 gap-2">
                    {timeSlots.map((slot) => (
                      <button
                        key={slot}
                        onClick={() => setSelectedSlot(selectedSlot === slot ? null : slot)}
                        className={`px-3 py-2.5 rounded-lg text-sm font-medium transition-all ${
                          selectedSlot === slot
                            ? "bg-primary text-primary-foreground glow-lime"
                            : "bg-muted/50 text-muted-foreground hover:bg-muted hover:text-foreground"
                        }`}
                      >
                        {slot}
                      </button>
                    ))}
                  </div>
                </motion.div>
              )}

              <div className="border-t border-border/50 pt-4 mb-4">
                <div className="flex justify-between text-sm mb-2">
                  <span className="text-muted-foreground">Session (1 hr)</span>
                  <span className="text-foreground">₹800</span>
                </div>
                <div className="flex justify-between text-sm mb-2">
                  <span className="text-muted-foreground">Platform fee</span>
                  <span className="text-foreground">₹50</span>
                </div>
                <div className="flex justify-between font-semibold text-foreground pt-2 border-t border-border/50">
                  <span>Total</span>
                  <span>₹850</span>
                </div>
              </div>

              <Button
                className="w-full h-12 bg-primary text-primary-foreground hover:bg-lime-glow font-semibold text-base"
                disabled={!selectedDate || !selectedSlot}
                onClick={() => setCheckoutOpen(true)}
              >
                Proceed to Checkout
              </Button>
            </div>
          </motion.div>
        </div>
      </div>

      <CheckoutDialog open={checkoutOpen} onOpenChange={setCheckoutOpen} />
    </div>
  );
};

export default TutorProfile;
