import { useState } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Label } from "@/components/ui/label";
import { toast } from "sonner";
import { Pencil } from "lucide-react";

interface EditProfileDialogProps {
  profile: {
    name: string;
    title: string;
    location: string;
    hourlyRate: number;
    about: string;
    experience: string;
  };
  onSave?: (profile: EditProfileDialogProps["profile"]) => void;
}

const EditProfileDialog = ({ profile, onSave }: EditProfileDialogProps) => {
  const [open, setOpen] = useState(false);
  const [form, setForm] = useState(profile);

  const handleSave = () => {
    onSave?.(form);
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
      <DialogContent className="glass border-border/50 sm:max-w-lg">
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
            <Textarea value={form.about} onChange={(e) => setForm({ ...form, about: e.target.value })} className="bg-muted/50 border-border/50 resize-none" rows={4} />
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
