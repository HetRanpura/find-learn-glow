import { useState } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import { toast } from "sonner";
import { Pencil, Plus, X } from "lucide-react";

interface Course {
  subject: string;
  syllabus: string;
  duration: string;
}

interface EditProfileDialogProps {
  profile: {
    name: string;
    title: string;
    location: string;
    hourlyRate: number;
    about: string;
    experience: string;
    courses?: Course[];
  };
  onSave?: (profile: EditProfileDialogProps["profile"]) => void;
}

const EditProfileDialog = ({ profile, onSave }: EditProfileDialogProps) => {
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState(profile);
  const [courses, setCourses] = useState<Course[]>(profile.courses || [
    { subject: "Calculus & Trigonometry", syllabus: "Limits, Derivatives, Integrals", duration: "3 months" },
  ]);

  const addCourse = () => {
    setCourses([...courses, { subject: "", syllabus: "", duration: "" }]);
  };

  const removeCourse = (index: number) => {
    setCourses(courses.filter((_, i) => i !== index));
  };

  const updateCourse = (index: number, field: keyof Course, value: string) => {
    const updated = [...courses];
    updated[index] = { ...updated[index], [field]: value };
    setCourses(updated);
  };

  const handleSave = () => {
    onSave?.({ ...form, courses });
    setOpen(false);
    toast.success("Profile updated successfully!");
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button variant="outline" size="sm" className="border-primary/50 text-primary hover:bg-primary/10">
          <Pencil className="w-4 h-4 mr-1" /> Edit Profile
        </Button>
      </DialogTrigger>
      <DialogContent className="glass border-border/50 sm:max-w-2xl max-h-[85vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle className="text-foreground">Edit Profile</DialogTitle>
        </DialogHeader>
        <div className="space-y-4 mt-2">
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label className="text-muted-foreground">Full Name</Label>
              <Input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} className="bg-muted/50 border-border/50" />
            </div>
            <div className="space-y-2">
              <Label className="text-muted-foreground">Title</Label>
              <Input value={form.title} onChange={(e) => setForm({ ...form, title: e.target.value })} className="bg-muted/50 border-border/50" />
            </div>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label className="text-muted-foreground">Location</Label>
              <Input value={form.location} onChange={(e) => setForm({ ...form, location: e.target.value })} className="bg-muted/50 border-border/50" />
            </div>
            <div className="space-y-2">
              <Label className="text-muted-foreground">Hourly Rate (₹)</Label>
              <Input type="number" value={form.hourlyRate} onChange={(e) => setForm({ ...form, hourlyRate: Number(e.target.value) })} className="bg-muted/50 border-border/50" />
            </div>
          </div>
          <div className="space-y-2">
            <Label className="text-muted-foreground">Experience</Label>
            <Input value={form.experience} onChange={(e) => setForm({ ...form, experience: e.target.value })} className="bg-muted/50 border-border/50" />
          </div>
          <div className="space-y-2">
            <Label className="text-muted-foreground">About</Label>
            <Textarea value={form.about} onChange={(e) => setForm({ ...form, about: e.target.value })} className="bg-muted/50 border-border/50 resize-none" rows={3} />
          </div>

          {/* Courses Section */}
          <div className="space-y-3 pt-2 border-t border-border/50">
            <div className="flex items-center justify-between">
              <Label className="text-foreground font-semibold">Courses Offered</Label>
              <Button type="button" variant="outline" size="sm" onClick={addCourse} className="border-primary/50 text-primary hover:bg-primary/10">
                <Plus className="w-3 h-3 mr-1" /> Add Course
              </Button>
            </div>
            {courses.map((course, i) => (
              <div key={i} className="border border-border/50 rounded-lg p-4 space-y-3 relative">
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  className="absolute top-2 right-2 h-6 w-6 text-muted-foreground hover:text-destructive"
                  onClick={() => removeCourse(i)}
                >
                  <X className="w-3 h-3" />
                </Button>
                <div className="space-y-2">
                  <Label className="text-muted-foreground text-xs">Subject / Course Name</Label>
                  <Input
                    value={course.subject}
                    onChange={(e) => updateCourse(i, "subject", e.target.value)}
                    placeholder="e.g. Basics of Python Programming"
                    className="bg-muted/50 border-border/50"
                  />
                </div>
                <div className="space-y-2">
                  <Label className="text-muted-foreground text-xs">Syllabus</Label>
                  <Textarea
                    value={course.syllabus}
                    onChange={(e) => updateCourse(i, "syllabus", e.target.value)}
                    placeholder="e.g. Variables, Loops, Functions, OOP basics"
                    className="bg-muted/50 border-border/50 resize-none"
                    rows={2}
                  />
                </div>
                <div className="space-y-2">
                  <Label className="text-muted-foreground text-xs">Duration</Label>
                  <Input
                    value={course.duration}
                    onChange={(e) => updateCourse(i, "duration", e.target.value)}
                    placeholder="e.g. 1 month"
                    className="bg-muted/50 border-border/50"
                  />
                </div>
              </div>
            ))}
          </div>

          <div className="flex gap-2 justify-end pt-2">
            <Button variant="ghost" onClick={() => setOpen(false)}>Cancel</Button>
            <Button onClick={handleSave} className="bg-primary text-primary-foreground hover:bg-primary/90">Save Changes</Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
};

export default EditProfileDialog;
