import { useState } from "react";
import { GraduationCap, ArrowLeft, Eye, EyeOff, Users, BookOpen } from "lucide-react";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Link, useNavigate } from "react-router-dom";
import { motion } from "framer-motion";
import { toast } from "sonner";

type Role = "student" | "tutor" | null;

const Signup = () => {
  const [role, setRole] = useState<Role>(null);
  const [showPassword, setShowPassword] = useState(false);
  const navigate = useNavigate();

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (role === "tutor") {
      navigate("/register");
    } else {
      toast.success("Student account created successfully!");
    }
  };

  return (
    <div className="min-h-screen bg-background">
      <nav className="fixed top-0 w-full z-50 glass">
        <div className="container mx-auto flex items-center justify-between py-4 px-6">
          <Link to="/" className="flex items-center gap-2">
            <GraduationCap className="w-8 h-8 text-primary" />
            <span className="text-xl font-bold text-foreground">TutorFind</span>
          </Link>
        </div>
      </nav>

      <div className="container mx-auto px-6 pt-28 pb-16 max-w-md">
        <Link to="/" className="inline-flex items-center gap-2 text-muted-foreground hover:text-primary transition-colors mb-8">
          <ArrowLeft className="w-4 h-4" /> Back to Home
        </Link>

        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
          <div className="glass rounded-2xl p-8">
            <h1 className="text-3xl font-bold text-foreground mb-2">Create Account</h1>
            <p className="text-muted-foreground mb-8">Join TutorFind today</p>

            {/* Role Selection */}
            <div className="grid grid-cols-2 gap-4 mb-8">
              <button
                type="button"
                onClick={() => setRole("student")}
                className={`flex flex-col items-center gap-3 p-6 rounded-xl border-2 transition-all ${
                  role === "student"
                    ? "border-primary bg-primary/10 glow-lime"
                    : "border-border bg-muted/30 hover:border-muted-foreground"
                }`}
              >
                <Users className={`w-8 h-8 ${role === "student" ? "text-primary" : "text-muted-foreground"}`} />
                <span className={`font-semibold ${role === "student" ? "text-primary" : "text-foreground"}`}>Student</span>
                <span className="text-xs text-muted-foreground">Find & book tutors</span>
              </button>
              <button
                type="button"
                onClick={() => setRole("tutor")}
                className={`flex flex-col items-center gap-3 p-6 rounded-xl border-2 transition-all ${
                  role === "tutor"
                    ? "border-secondary bg-secondary/10 glow-cyan"
                    : "border-border bg-muted/30 hover:border-muted-foreground"
                }`}
              >
                <BookOpen className={`w-8 h-8 ${role === "tutor" ? "text-secondary" : "text-muted-foreground"}`} />
                <span className={`font-semibold ${role === "tutor" ? "text-secondary" : "text-foreground"}`}>Tutor</span>
                <span className="text-xs text-muted-foreground">Teach & earn</span>
              </button>
            </div>

            {role && (
              <motion.form
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                onSubmit={handleSubmit}
                className="space-y-5"
              >
                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <Label className="text-foreground">First Name</Label>
                    <Input placeholder="First name" className="bg-muted/50 border-border text-foreground placeholder:text-muted-foreground focus-visible:ring-primary" />
                  </div>
                  <div className="space-y-2">
                    <Label className="text-foreground">Last Name</Label>
                    <Input placeholder="Last name" className="bg-muted/50 border-border text-foreground placeholder:text-muted-foreground focus-visible:ring-primary" />
                  </div>
                </div>

                <div className="space-y-2">
                  <Label className="text-foreground">Email</Label>
                  <Input type="email" placeholder="you@example.com" className="bg-muted/50 border-border text-foreground placeholder:text-muted-foreground focus-visible:ring-primary" />
                </div>

                <div className="space-y-2">
                  <Label className="text-foreground">Phone Number</Label>
                  <Input type="tel" placeholder="+91 98765 43210" className="bg-muted/50 border-border text-foreground placeholder:text-muted-foreground focus-visible:ring-primary" />
                </div>

                <div className="space-y-2">
                  <Label className="text-foreground">Password</Label>
                  <div className="relative">
                    <Input
                      type={showPassword ? "text" : "password"}
                      placeholder="Create a strong password"
                      className="bg-muted/50 border-border text-foreground placeholder:text-muted-foreground focus-visible:ring-primary pr-10"
                    />
                    <button type="button" onClick={() => setShowPassword(!showPassword)} className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground">
                      {showPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                    </button>
                  </div>
                </div>

                <Button type="submit" className="w-full h-12 bg-primary text-primary-foreground hover:bg-lime-glow font-semibold text-base">
                  {role === "tutor" ? "Continue as Tutor →" : "Create Student Account"}
                </Button>

                {role === "tutor" && (
                  <p className="text-xs text-center text-muted-foreground">
                    You'll complete your tutor profile on the next step.
                  </p>
                )}

                <p className="text-center text-sm text-muted-foreground">
                  Already have an account?{" "}
                  <Link to="/login" className="text-primary hover:underline">Log In</Link>
                </p>
              </motion.form>
            )}
          </div>
        </motion.div>
      </div>
    </div>
  );
};

export default Signup;
