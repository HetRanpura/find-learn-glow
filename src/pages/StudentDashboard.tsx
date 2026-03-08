import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { Link } from "react-router-dom";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import { Progress } from "@/components/ui/progress";
import { Textarea } from "@/components/ui/textarea";
import {
  ArrowLeft,
  Calendar,
  Clock,
  Star,
  BookOpen,
  History,
  MessageSquare,
  MapPin,
  Video,
  IndianRupee,
  CheckCircle2,
  XCircle,
  Send,
} from "lucide-react";

const upcomingSessions = [
  {
    id: 1,
    tutor: "Dr. Priya Sharma",
    avatar: "",
    initials: "PS",
    subject: "Mathematics",
    date: "2026-03-10",
    time: "4:00 PM - 5:00 PM",
    mode: "Online",
    status: "confirmed",
    fee: 800,
  },
  {
    id: 2,
    tutor: "Rahul Verma",
    avatar: "",
    initials: "RV",
    subject: "Physics",
    date: "2026-03-11",
    time: "6:00 PM - 7:30 PM",
    mode: "In-Person",
    location: "Sector 15, Noida",
    status: "confirmed",
    fee: 1000,
  },
  {
    id: 3,
    tutor: "Anita Desai",
    avatar: "",
    initials: "AD",
    subject: "English Literature",
    date: "2026-03-13",
    time: "3:00 PM - 4:00 PM",
    mode: "Online",
    status: "pending",
    fee: 600,
  },
];

const bookingHistory = [
  {
    id: 1,
    tutor: "Dr. Priya Sharma",
    initials: "PS",
    subject: "Mathematics",
    date: "2026-03-01",
    time: "4:00 PM - 5:00 PM",
    status: "completed",
    fee: 800,
    reviewed: true,
  },
  {
    id: 2,
    tutor: "Rahul Verma",
    initials: "RV",
    subject: "Physics",
    date: "2026-02-28",
    time: "6:00 PM - 7:30 PM",
    status: "completed",
    fee: 1000,
    reviewed: false,
  },
  {
    id: 3,
    tutor: "Dr. Priya Sharma",
    initials: "PS",
    subject: "Mathematics",
    date: "2026-02-25",
    time: "4:00 PM - 5:00 PM",
    status: "completed",
    fee: 800,
    reviewed: true,
  },
  {
    id: 4,
    tutor: "Anita Desai",
    initials: "AD",
    subject: "English Literature",
    date: "2026-02-20",
    time: "3:00 PM - 4:00 PM",
    status: "cancelled",
    fee: 600,
    reviewed: false,
  },
  {
    id: 5,
    tutor: "Suresh Iyer",
    initials: "SI",
    subject: "Chemistry",
    date: "2026-02-18",
    time: "5:00 PM - 6:00 PM",
    status: "completed",
    fee: 750,
    reviewed: true,
  },
];

const myReviews = [
  {
    id: 1,
    tutor: "Dr. Priya Sharma",
    initials: "PS",
    subject: "Mathematics",
    rating: 5,
    date: "2026-03-02",
    comment: "Excellent teaching style! Made calculus concepts very easy to understand. Highly recommend.",
  },
  {
    id: 2,
    tutor: "Suresh Iyer",
    initials: "SI",
    subject: "Chemistry",
    rating: 4,
    date: "2026-02-19",
    comment: "Good session on organic chemistry. Would appreciate more practice problems next time.",
  },
  {
    id: 3,
    tutor: "Dr. Priya Sharma",
    initials: "PS",
    subject: "Mathematics",
    rating: 5,
    date: "2026-02-26",
    comment: "Another great session. Patient and thorough explanations.",
  },
];

const StatusBadge = ({ status }: { status: string }) => {
  const styles: Record<string, string> = {
    confirmed: "bg-primary/20 text-primary border-primary/30",
    pending: "bg-accent/20 text-accent border-accent/30",
    completed: "bg-primary/20 text-primary border-primary/30",
    cancelled: "bg-destructive/20 text-destructive border-destructive/30",
  };
  return (
    <Badge variant="outline" className={styles[status] || ""}>
      {status.charAt(0).toUpperCase() + status.slice(1)}
    </Badge>
  );
};

const StarRating = ({ rating, interactive = false, onChange }: { rating: number; interactive?: boolean; onChange?: (r: number) => void }) => (
  <div className="flex gap-1">
    {[1, 2, 3, 4, 5].map((s) => (
      <Star
        key={s}
        className={`h-4 w-4 ${s <= rating ? "fill-primary text-primary" : "text-muted-foreground/40"} ${interactive ? "cursor-pointer hover:text-primary" : ""}`}
        onClick={() => interactive && onChange?.(s)}
      />
    ))}
  </div>
);

const StudentDashboard = () => {
  const [reviewTutor, setReviewTutor] = useState<number | null>(null);
  const [newRating, setNewRating] = useState(0);
  const [newComment, setNewComment] = useState("");

  const totalSpent = bookingHistory.filter((b) => b.status === "completed").reduce((s, b) => s + b.fee, 0);
  const totalSessions = bookingHistory.filter((b) => b.status === "completed").length;

  const fadeUp = {
    initial: { opacity: 0, y: 16 },
    animate: { opacity: 1, y: 0 },
    exit: { opacity: 0, y: -16 },
    transition: { duration: 0.3 },
  };

  return (
    <div className="min-h-screen bg-background">
      {/* Header */}
      <header className="border-b border-border/50 glass sticky top-0 z-30">
        <div className="container mx-auto px-4 py-4 flex items-center justify-between">
          <div className="flex items-center gap-4">
            <Link to="/">
              <Button variant="ghost" size="icon">
                <ArrowLeft className="h-5 w-5" />
              </Button>
            </Link>
            <div>
              <h1 className="text-xl font-bold">Student Dashboard</h1>
              <p className="text-sm text-muted-foreground">Welcome back, Arjun</p>
            </div>
          </div>
          <Avatar className="h-10 w-10 border-2 border-primary/50">
            <AvatarFallback className="bg-primary/20 text-primary font-bold">AK</AvatarFallback>
          </Avatar>
        </div>
      </header>

      <main className="container mx-auto px-4 py-8 space-y-8">
        {/* Stats */}
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
          {[
            { label: "Upcoming Sessions", value: upcomingSessions.length, icon: Calendar, color: "text-accent" },
            { label: "Total Sessions", value: totalSessions, icon: BookOpen, color: "text-primary" },
            { label: "Total Spent", value: `₹${totalSpent.toLocaleString()}`, icon: IndianRupee, color: "text-primary" },
          ].map((stat, i) => (
            <motion.div key={i} {...fadeUp} transition={{ delay: i * 0.1 }}>
              <Card className="glass border-border/50">
                <CardContent className="p-5 flex items-center gap-4">
                  <div className={`p-3 rounded-xl bg-muted ${stat.color}`}>
                    <stat.icon className="h-5 w-5" />
                  </div>
                  <div>
                    <p className="text-2xl font-bold">{stat.value}</p>
                    <p className="text-xs text-muted-foreground">{stat.label}</p>
                  </div>
                </CardContent>
              </Card>
            </motion.div>
          ))}
        </div>

        {/* Tabs */}
        <Tabs defaultValue="upcoming" className="space-y-6">
          <TabsList className="bg-muted/50 border border-border/50">
            <TabsTrigger value="upcoming" className="data-[state=active]:bg-primary data-[state=active]:text-primary-foreground">
              <Calendar className="h-4 w-4 mr-2" /> Upcoming
            </TabsTrigger>
            <TabsTrigger value="history" className="data-[state=active]:bg-primary data-[state=active]:text-primary-foreground">
              <History className="h-4 w-4 mr-2" /> History
            </TabsTrigger>
            <TabsTrigger value="reviews" className="data-[state=active]:bg-primary data-[state=active]:text-primary-foreground">
              <MessageSquare className="h-4 w-4 mr-2" /> My Reviews
            </TabsTrigger>
          </TabsList>

          {/* Upcoming Sessions */}
          <AnimatePresence mode="wait">
            <TabsContent value="upcoming">
              <motion.div {...fadeUp} className="space-y-4">
                {upcomingSessions.map((session) => (
                  <Card key={session.id} className="glass border-border/50 hover:border-primary/30 transition-colors">
                    <CardContent className="p-5">
                      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div className="flex items-start gap-4">
                          <Avatar className="h-12 w-12 border border-border">
                            <AvatarFallback className="bg-muted text-foreground font-semibold">{session.initials}</AvatarFallback>
                          </Avatar>
                          <div className="space-y-1">
                            <p className="font-semibold">{session.tutor}</p>
                            <p className="text-sm text-accent">{session.subject}</p>
                            <div className="flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                              <span className="flex items-center gap-1"><Calendar className="h-3 w-3" />{session.date}</span>
                              <span className="flex items-center gap-1"><Clock className="h-3 w-3" />{session.time}</span>
                              <span className="flex items-center gap-1">
                                {session.mode === "Online" ? <Video className="h-3 w-3" /> : <MapPin className="h-3 w-3" />}
                                {session.mode === "In-Person" ? session.location : session.mode}
                              </span>
                            </div>
                          </div>
                        </div>
                        <div className="flex items-center gap-3">
                          <StatusBadge status={session.status} />
                          <span className="font-bold text-primary">₹{session.fee}</span>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                ))}
              </motion.div>
            </TabsContent>

            {/* Booking History */}
            <TabsContent value="history">
              <motion.div {...fadeUp} className="space-y-4">
                {bookingHistory.map((booking) => (
                  <Card key={booking.id} className="glass border-border/50">
                    <CardContent className="p-5">
                      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div className="flex items-start gap-4">
                          <Avatar className="h-12 w-12 border border-border">
                            <AvatarFallback className="bg-muted text-foreground font-semibold">{booking.initials}</AvatarFallback>
                          </Avatar>
                          <div className="space-y-1">
                            <p className="font-semibold">{booking.tutor}</p>
                            <p className="text-sm text-accent">{booking.subject}</p>
                            <div className="flex items-center gap-3 text-xs text-muted-foreground">
                              <span className="flex items-center gap-1"><Calendar className="h-3 w-3" />{booking.date}</span>
                              <span className="flex items-center gap-1"><Clock className="h-3 w-3" />{booking.time}</span>
                            </div>
                          </div>
                        </div>
                        <div className="flex items-center gap-3">
                          <StatusBadge status={booking.status} />
                          {booking.status === "completed" && !booking.reviewed && (
                            <Button size="sm" variant="outline" className="border-primary/50 text-primary hover:bg-primary/10" onClick={() => { setReviewTutor(booking.id); setNewRating(0); setNewComment(""); }}>
                              <Star className="h-3 w-3 mr-1" /> Review
                            </Button>
                          )}
                          {booking.reviewed && (
                            <span className="text-xs text-primary flex items-center gap-1"><CheckCircle2 className="h-3 w-3" />Reviewed</span>
                          )}
                          <span className={`font-bold ${booking.status === "cancelled" ? "text-destructive line-through" : "text-primary"}`}>₹{booking.fee}</span>
                        </div>
                      </div>

                      {/* Inline review form */}
                      <AnimatePresence>
                        {reviewTutor === booking.id && (
                          <motion.div initial={{ height: 0, opacity: 0 }} animate={{ height: "auto", opacity: 1 }} exit={{ height: 0, opacity: 0 }} className="overflow-hidden">
                            <div className="mt-4 pt-4 border-t border-border/50 space-y-3">
                              <div className="flex items-center gap-3">
                                <span className="text-sm text-muted-foreground">Your rating:</span>
                                <StarRating rating={newRating} interactive onChange={setNewRating} />
                              </div>
                              <Textarea placeholder="Share your experience..." value={newComment} onChange={(e) => setNewComment(e.target.value)} className="bg-muted/50 border-border/50 resize-none" rows={3} />
                              <div className="flex gap-2 justify-end">
                                <Button size="sm" variant="ghost" onClick={() => setReviewTutor(null)}>Cancel</Button>
                                <Button size="sm" className="bg-primary text-primary-foreground hover:bg-primary/90">
                                  <Send className="h-3 w-3 mr-1" /> Submit
                                </Button>
                              </div>
                            </div>
                          </motion.div>
                        )}
                      </AnimatePresence>
                    </CardContent>
                  </Card>
                ))}
              </motion.div>
            </TabsContent>

            {/* My Reviews */}
            <TabsContent value="reviews">
              <motion.div {...fadeUp} className="space-y-4">
                {myReviews.map((review) => (
                  <Card key={review.id} className="glass border-border/50">
                    <CardContent className="p-5 space-y-3">
                      <div className="flex items-center justify-between">
                        <div className="flex items-center gap-3">
                          <Avatar className="h-10 w-10 border border-border">
                            <AvatarFallback className="bg-muted text-foreground font-semibold">{review.initials}</AvatarFallback>
                          </Avatar>
                          <div>
                            <p className="font-semibold">{review.tutor}</p>
                            <p className="text-xs text-accent">{review.subject}</p>
                          </div>
                        </div>
                        <span className="text-xs text-muted-foreground">{review.date}</span>
                      </div>
                      <StarRating rating={review.rating} />
                      <p className="text-sm text-muted-foreground leading-relaxed">{review.comment}</p>
                    </CardContent>
                  </Card>
                ))}
              </motion.div>
            </TabsContent>
          </AnimatePresence>
        </Tabs>
      </main>
    </div>
  );
};

export default StudentDashboard;
