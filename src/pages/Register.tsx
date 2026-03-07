import { useState, useRef } from "react";
import { Upload, X, GraduationCap, ArrowLeft, Eye, EyeOff } from "lucide-react";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Link } from "react-router-dom";
import { motion } from "framer-motion";
import { toast } from "sonner";

const Register = () => {
  const [files, setFiles] = useState<File[]>([]);
  const [dragActive, setDragActive] = useState(false);
  const [showPassword, setShowPassword] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const handleDrag = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setDragActive(e.type === "dragenter" || e.type === "dragover");
  };

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault();
    e.stopPropagation();
    setDragActive(false);
    if (e.dataTransfer.files) {
      setFiles((prev) => [...prev, ...Array.from(e.dataTransfer.files)]);
    }
  };

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    if (e.target.files) {
      setFiles((prev) => [...prev, ...Array.from(e.target.files!)]);
    }
  };

  const removeFile = (index: number) => {
    setFiles((prev) => prev.filter((_, i) => i !== index));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    toast.success("Registration submitted successfully!");
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

      <div className="container mx-auto px-6 pt-28 pb-16 max-w-2xl">
        <Link to="/" className="inline-flex items-center gap-2 text-muted-foreground hover:text-primary transition-colors mb-8">
          <ArrowLeft className="w-4 h-4" /> Back to Home
        </Link>

        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }}>
          <div className="glass rounded-2xl p-8">
            <h1 className="text-3xl font-bold text-foreground mb-2">Register as a Tutor</h1>
            <p className="text-muted-foreground mb-8">Join our platform and start teaching students near you.</p>

            <form onSubmit={handleSubmit} className="space-y-6">
              <div className="grid sm:grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label className="text-foreground">First Name</Label>
                  <Input placeholder="Ananya" className="bg-muted/50 border-border text-foreground placeholder:text-muted-foreground focus-visible:ring-primary" />
                </div>
                <div className="space-y-2">
                  <Label className="text-foreground">Last Name</Label>
                  <Input placeholder="Sharma" className="bg-muted/50 border-border text-foreground placeholder:text-muted-foreground focus-visible:ring-primary" />
                </div>
              </div>

              <div className="space-y-2">
                <Label className="text-foreground">Email</Label>
                <Input type="email" placeholder="ananya@example.com" className="bg-muted/50 border-border text-foreground placeholder:text-muted-foreground focus-visible:ring-primary" />
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

              <div className="space-y-2">
                <Label className="text-foreground">Primary Subject</Label>
                <Select>
                  <SelectTrigger className="bg-muted/50 border-border text-foreground focus:ring-primary">
                    <SelectValue placeholder="Select your subject" />
                  </SelectTrigger>
                  <SelectContent className="bg-popover border-border">
                    {["Mathematics", "Physics", "Chemistry", "English", "Biology", "Computer Science"].map((s) => (
                      <SelectItem key={s} value={s.toLowerCase()}>{s}</SelectItem>
                    ))}
                  </SelectContent>
                </Select>
              </div>

              <div className="space-y-2">
                <Label className="text-foreground">Hourly Rate (₹)</Label>
                <Input type="number" placeholder="800" className="bg-muted/50 border-border text-foreground placeholder:text-muted-foreground focus-visible:ring-primary" />
              </div>

              <div className="space-y-2">
                <Label className="text-foreground">Bio</Label>
                <Textarea placeholder="Tell students about your teaching experience and methodology..." rows={4} className="bg-muted/50 border-border text-foreground placeholder:text-muted-foreground focus-visible:ring-primary resize-none" />
              </div>

              {/* File Upload */}
              <div className="space-y-2">
                <Label className="text-foreground">Upload Certificates & Documents</Label>
                <div
                  onDragEnter={handleDrag}
                  onDragLeave={handleDrag}
                  onDragOver={handleDrag}
                  onDrop={handleDrop}
                  onClick={() => fileInputRef.current?.click()}
                  className={`border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-all ${
                    dragActive
                      ? "border-primary bg-primary/5"
                      : "border-border hover:border-muted-foreground"
                  }`}
                >
                  <input
                    ref={fileInputRef}
                    type="file"
                    multiple
                    accept=".pdf,.jpg,.jpeg,.png"
                    onChange={handleFileChange}
                    className="hidden"
                  />
                  <Upload className={`w-10 h-10 mx-auto mb-3 ${dragActive ? "text-primary" : "text-muted-foreground"}`} />
                  <p className="text-foreground font-medium">
                    {dragActive ? "Drop files here" : "Drag & drop files or click to browse"}
                  </p>
                  <p className="text-sm text-muted-foreground mt-1">PDF, JPG, PNG up to 10MB each</p>
                </div>

                {files.length > 0 && (
                  <div className="space-y-2 mt-3">
                    {files.map((file, i) => (
                      <div key={i} className="flex items-center justify-between bg-muted/50 rounded-lg px-4 py-2.5">
                        <span className="text-sm text-foreground truncate">{file.name}</span>
                        <button type="button" onClick={() => removeFile(i)} className="text-muted-foreground hover:text-destructive ml-3 shrink-0">
                          <X className="w-4 h-4" />
                        </button>
                      </div>
                    ))}
                  </div>
                )}
              </div>

              <Button type="submit" className="w-full h-12 bg-primary text-primary-foreground hover:bg-lime-glow font-semibold text-base">
                Submit Registration
              </Button>
            </form>
          </div>
        </motion.div>
      </div>
    </div>
  );
};

export default Register;
