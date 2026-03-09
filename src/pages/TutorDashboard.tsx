import { useState } from "react";
import { Link } from "react-router-dom";
import { motion } from "framer-motion";
import {
  GraduationCap, Calendar, DollarSign, Users, Clock, CheckCircle, XCircle,
  TrendingUp, BookOpen, Star, Bell, ChevronRight, BarChart3, ArrowUpRight,
  ArrowDownRight, MessageSquare
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
import EditProfileDialog from "@/components/EditProfileDialog";
import ReviewDialog from "@/components/ReviewDialog";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Badge } from "@/components/ui/badge";
import { Progress } from "@/components/ui/progress";
import { Calendar as CalendarWidget } from "@/components/ui/calendar";

const weekSchedule = [
  { day: "Mon", date: "10", slots: [
    { time: "9:00 AM", student: "Aarav Patel", subject: "Mathematics", status: "confirmed" },
    { time: "11:00 AM", student: "Ishita Sharma", subject: "Mathematics", status: "confirmed" },
    { time: "2:00 PM", student: "Rohan Mehta", subject: "Physics", status: "pending" },
  ]},
  { day: "Tue", date: "11", slots: [
    { time: "10:00 AM", student: "Priya Das", subject: "Mathematics", status: "confirmed" },
    { time: "3:00 PM", student: "Kavya Nair", subject: "Chemistry", status: "confirmed" },
  ]},
  { day: "Wed", date: "12", slots: [
    { time: "9:00 AM", student: "Arjun Reddy", subject: "Physics", status: "confirmed" },
    { time: "1:00 PM", student: "Sanya Gupta", subject: "Mathematics", status: "pending" },
    { time: "4:00 PM", student: "Vikram Joshi", subject: "Chemistry", status: "confirmed" },
  ]},
  { day: "Thu", date: "13", slots: [
    { time: "10:00 AM", student: "Meera Iyer", subject: "Mathematics", status: "confirmed" },
  ]},
  { day: "Fri", date: "14", slots: [
    { time: "9:00 AM", student: "Rahul Verma", subject: "Physics", status: "confirmed" },
    { time: "11:00 AM", student: "Ananya Kapoor", subject: "Mathematics", status: "confirmed" },
    { time: "2:00 PM", student: "Dev Malhotra", subject: "Chemistry", status: "pending" },
  ]},
];

const studentRequests = [
  { id: 1, name: "Aditi Sharma", subject: "Mathematics", grade: "Class 10", message: "Need help with calculus and trigonometry for board exams.", date: "2 hours ago", avatar: "AS", sessions: 3 },
  { id: 2, name: "Karan Singh", subject: "Physics", grade: "Class 12", message: "Looking for a tutor for mechanics and optics, 2 sessions/week.", date: "5 hours ago", avatar: "KS", sessions: 2 },
  { id: 3, name: "Sneha Reddy", subject: "Chemistry", grade: "Class 11", message: "Organic chemistry preparation for JEE Mains.", date: "1 day ago", avatar: "SR", sessions: 4 },
  { id: 4, name: "Amit Patel", subject: "Mathematics", grade: "Class 9", message: "Algebra and geometry basics, need weekend slots.", date: "1 day ago", avatar: "AP", sessions: 2 },
];

const earningsData = {
  thisMonth: 24800,
  lastMonth: 21500,
  totalStudents: 18,
  completedSessions: 42,
  upcomingSessions: 13,
  averageRating: 4.9,
  weeklyBreakdown: [
    { week: "Week 1", amount: 5600 },
    { week: "Week 2", amount: 6200 },
    { week: "Week 3", amount: 7400 },
    { week: "Week 4", amount: 5600 },
  ],
  subjectBreakdown: [
    { subject: "Mathematics", percentage: 55, amount: 13640 },
    { subject: "Physics", percentage: 28, amount: 6944 },
    { subject: "Chemistry", percentage: 17, amount: 4216 },
  ],
};

const TutorDashboard = () => {
  const [selectedDate, setSelectedDate] = useState<Date | undefined>(new Date());
  const [selectedDay, setSelectedDay] = useState(0);
  const growthPercent = (((earningsData.thisMonth - earningsData.lastMonth) / earningsData.lastMonth) * 100).toFixed(1);

  return (
    <div className="min-h-screen bg-background">
      {/* Navbar */}
      <nav className="fixed top-0 w-full z-50 glass">
        <div className="container mx-auto flex items-center justify-between py-4 px-6">
          <Link to="/" className="flex items-center gap-2">
            <GraduationCap className="w-8 h-8 text-primary" />
            <span className="text-xl font-bold text-foreground">TutorFind</span>
          </Link>
          <div className="flex items-center gap-4">
            <Button variant="ghost" size="icon" className="relative">
              <Bell className="w-5 h-5 text-muted-foreground" />
              <span className="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-destructive text-destructive-foreground text-[10px] flex items-center justify-center">3</span>
            </Button>
            <div className="flex items-center gap-3">
              <div className="w-9 h-9 rounded-full bg-primary/20 border border-primary/30 flex items-center justify-center text-sm font-semibold text-primary">AS</div>
              <div className="hidden md:block">
                <p className="text-sm font-medium text-foreground">Dr. Ananya Sharma</p>
                <p className="text-xs text-muted-foreground">Mathematics Tutor</p>
              </div>
            </div>
          </div>
        </div>
      </nav>

      <main className="pt-24 pb-12 container mx-auto px-6">
        {/* Header */}
        <motion.div initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} className="mb-8">
          <h1 className="text-3xl font-bold text-foreground mb-1">Dashboard</h1>
          <p className="text-muted-foreground">Welcome back, Dr. Sharma. Here's your overview.</p>
        </motion.div>

        {/* Stat Cards */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          {[
            { label: "This Month", value: `₹${earningsData.thisMonth.toLocaleString()}`, icon: DollarSign, change: `+${growthPercent}%`, up: true, color: "text-primary" },
            { label: "Active Students", value: earningsData.totalStudents, icon: Users, change: "+3 new", up: true, color: "text-secondary" },
            { label: "Sessions Done", value: earningsData.completedSessions, icon: CheckCircle, change: "This month", up: true, color: "text-primary" },
            { label: "Avg Rating", value: earningsData.averageRating, icon: Star, change: "128 reviews", up: true, color: "text-yellow-400" },
          ].map((stat, i) => (
            <motion.div key={stat.label} initial={{ opacity: 0, y: 20 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: i * 0.1 }}>
              <Card className="glass border-border/50 hover:border-primary/30 transition-colors">
                <CardContent className="p-5">
                  <div className="flex items-start justify-between mb-3">
                    <div className={`w-10 h-10 rounded-lg bg-muted flex items-center justify-center`}>
                      <stat.icon className={`w-5 h-5 ${stat.color}`} />
                    </div>
                    <span className="flex items-center gap-1 text-xs text-primary">
                      <ArrowUpRight className="w-3 h-3" />
                      {stat.change}
                    </span>
                  </div>
                  <p className="text-2xl font-bold text-foreground">{stat.value}</p>
                  <p className="text-sm text-muted-foreground mt-1">{stat.label}</p>
                </CardContent>
              </Card>
            </motion.div>
          ))}
        </div>

        {/* Main Content Tabs */}
        <Tabs defaultValue="schedule" className="space-y-6">
          <TabsList className="bg-muted/50 border border-border/50">
            <TabsTrigger value="schedule" className="data-[state=active]:bg-primary data-[state=active]:text-primary-foreground">
              <Calendar className="w-4 h-4 mr-2" /> Schedule
            </TabsTrigger>
            <TabsTrigger value="earnings" className="data-[state=active]:bg-primary data-[state=active]:text-primary-foreground">
              <BarChart3 className="w-4 h-4 mr-2" /> Earnings
            </TabsTrigger>
            <TabsTrigger value="requests" className="data-[state=active]:bg-primary data-[state=active]:text-primary-foreground">
              <MessageSquare className="w-4 h-4 mr-2" /> Requests
              <Badge className="ml-2 bg-destructive text-destructive-foreground text-[10px] px-1.5 py-0">4</Badge>
            </TabsTrigger>
          </TabsList>

          {/* Schedule Tab */}
          <TabsContent value="schedule">
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              {/* Calendar */}
              <Card className="glass border-border/50">
                <CardHeader className="pb-3">
                  <CardTitle className="text-lg text-foreground">Calendar</CardTitle>
                </CardHeader>
                <CardContent>
                  <CalendarWidget
                    mode="single"
                    selected={selectedDate}
                    onSelect={setSelectedDate}
                    className="rounded-md pointer-events-auto"
                  />
                  <div className="mt-4 space-y-2">
                    <div className="flex items-center gap-2 text-sm">
                      <div className="w-3 h-3 rounded-full bg-primary" />
                      <span className="text-muted-foreground">Confirmed sessions</span>
                    </div>
                    <div className="flex items-center gap-2 text-sm">
                      <div className="w-3 h-3 rounded-full bg-secondary" />
                      <span className="text-muted-foreground">Pending approval</span>
                    </div>
                  </div>
                </CardContent>
              </Card>

              {/* Weekly Schedule */}
              <div className="lg:col-span-2 space-y-4">
                {/* Day selector */}
                <div className="flex gap-2 overflow-x-auto pb-2">
                  {weekSchedule.map((day, i) => (
                    <button
                      key={day.day}
                      onClick={() => setSelectedDay(i)}
                      className={`flex flex-col items-center px-4 py-3 rounded-lg border transition-all min-w-[70px] ${
                        selectedDay === i
                          ? "bg-primary text-primary-foreground border-primary glow-lime"
                          : "bg-muted/50 text-muted-foreground border-border/50 hover:border-primary/30"
                      }`}
                    >
                      <span className="text-xs font-medium">{day.day}</span>
                      <span className="text-lg font-bold">{day.date}</span>
                      <span className="text-xs mt-1">{day.slots.length} slots</span>
                    </button>
                  ))}
                </div>

                {/* Slots */}
                <div className="space-y-3">
                  {weekSchedule[selectedDay].slots.map((slot, i) => (
                    <motion.div key={i} initial={{ opacity: 0, x: 20 }} animate={{ opacity: 1, x: 0 }} transition={{ delay: i * 0.05 }}>
                      <Card className="glass border-border/50 hover:border-primary/20 transition-colors">
                        <CardContent className="p-4 flex items-center justify-between">
                          <div className="flex items-center gap-4">
                            <div className="text-center min-w-[60px]">
                              <Clock className="w-4 h-4 mx-auto text-secondary mb-1" />
                              <span className="text-sm font-mono font-medium text-foreground">{slot.time}</span>
                            </div>
                            <div className="h-10 w-px bg-border" />
                            <div>
                              <p className="font-medium text-foreground">{slot.student}</p>
                              <p className="text-sm text-muted-foreground">{slot.subject} • 1 hour session</p>
                            </div>
                          </div>
                          <div className="flex items-center gap-2">
                            <Badge className={slot.status === "confirmed"
                              ? "bg-primary/10 text-primary border border-primary/20"
                              : slot.status === "completed"
                              ? "bg-primary/20 text-primary border border-primary/30"
                              : "bg-secondary/10 text-secondary border border-secondary/20"
                            }>
                              {slot.status === "confirmed" ? "Confirmed" : slot.status === "completed" ? "Completed" : "Pending"}
                            </Badge>
                            {slot.status === "pending" && (
                              <div className="flex gap-1">
                                <Button size="icon" variant="ghost" className="h-8 w-8 text-primary hover:bg-primary/10">
                                  <CheckCircle className="w-4 h-4" />
                                </Button>
                                <Button size="icon" variant="ghost" className="h-8 w-8 text-destructive hover:bg-destructive/10">
                                  <XCircle className="w-4 h-4" />
                                </Button>
                              </div>
                            )}
                            {slot.status === "confirmed" && (
                              <Button size="sm" variant="outline" className="h-8 border-primary/50 text-primary hover:bg-primary/10 text-xs">
                                <CheckCircle className="w-3 h-3 mr-1" /> Mark Complete
                              </Button>
                            )}
                            {slot.status === "completed" && (
                              <Button size="sm" variant="outline" className="h-8 border-primary/50 text-primary hover:bg-primary/10 text-xs">
                                <Star className="w-3 h-3 mr-1" /> Review
                              </Button>
                            )}
                          </div>
                        </CardContent>
                      </Card>
                    </motion.div>
                  ))}
                </div>
              </div>
            </div>
          </TabsContent>

          {/* Earnings Tab */}
          <TabsContent value="earnings">
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              {/* Earnings summary */}
              <div className="lg:col-span-2 space-y-6">
                <Card className="glass border-border/50">
                  <CardHeader>
                    <CardTitle className="text-lg text-foreground">Monthly Earnings</CardTitle>
                    <CardDescription className="text-muted-foreground">Weekly breakdown for this month</CardDescription>
                  </CardHeader>
                  <CardContent>
                    <div className="space-y-4">
                      {earningsData.weeklyBreakdown.map((week, i) => {
                        const maxAmount = Math.max(...earningsData.weeklyBreakdown.map(w => w.amount));
                        const pct = (week.amount / maxAmount) * 100;
                        return (
                          <div key={week.week} className="space-y-2">
                            <div className="flex justify-between text-sm">
                              <span className="text-muted-foreground">{week.week}</span>
                              <span className="font-mono font-medium text-foreground">₹{week.amount.toLocaleString()}</span>
                            </div>
                            <div className="h-3 rounded-full bg-muted overflow-hidden">
                              <motion.div
                                initial={{ width: 0 }}
                                animate={{ width: `${pct}%` }}
                                transition={{ delay: i * 0.15, duration: 0.6 }}
                                className="h-full rounded-full"
                                style={{ background: "var(--gradient-primary)" }}
                              />
                            </div>
                          </div>
                        );
                      })}
                    </div>
                    <div className="mt-6 pt-4 border-t border-border/50 flex justify-between items-center">
                      <span className="text-muted-foreground">Total this month</span>
                      <span className="text-2xl font-bold text-foreground">₹{earningsData.thisMonth.toLocaleString()}</span>
                    </div>
                  </CardContent>
                </Card>

                {/* Subject breakdown */}
                <Card className="glass border-border/50">
                  <CardHeader>
                    <CardTitle className="text-lg text-foreground">Earnings by Subject</CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    {earningsData.subjectBreakdown.map((item) => (
                      <div key={item.subject} className="space-y-2">
                        <div className="flex justify-between text-sm">
                          <div className="flex items-center gap-2">
                            <BookOpen className="w-4 h-4 text-secondary" />
                            <span className="text-foreground">{item.subject}</span>
                          </div>
                          <div className="flex items-center gap-3">
                            <span className="text-muted-foreground">{item.percentage}%</span>
                            <span className="font-mono font-medium text-foreground">₹{item.amount.toLocaleString()}</span>
                          </div>
                        </div>
                        <Progress value={item.percentage} className="h-2" />
                      </div>
                    ))}
                  </CardContent>
                </Card>
              </div>

              {/* Side stats */}
              <div className="space-y-4">
                <Card className="glass border-border/50 border-l-4 border-l-primary">
                  <CardContent className="p-5">
                    <div className="flex items-center gap-3 mb-3">
                      <TrendingUp className="w-5 h-5 text-primary" />
                      <span className="text-sm text-muted-foreground">Growth</span>
                    </div>
                    <p className="text-3xl font-bold text-foreground">+{growthPercent}%</p>
                    <p className="text-sm text-muted-foreground mt-1">vs last month (₹{earningsData.lastMonth.toLocaleString()})</p>
                  </CardContent>
                </Card>

                <Card className="glass border-border/50 border-l-4 border-l-secondary">
                  <CardContent className="p-5">
                    <div className="flex items-center gap-3 mb-3">
                      <Clock className="w-5 h-5 text-secondary" />
                      <span className="text-sm text-muted-foreground">Upcoming</span>
                    </div>
                    <p className="text-3xl font-bold text-foreground">{earningsData.upcomingSessions}</p>
                    <p className="text-sm text-muted-foreground mt-1">sessions this week</p>
                  </CardContent>
                </Card>

                <Card className="glass border-border/50 border-l-4 border-l-primary">
                  <CardContent className="p-5">
                    <div className="flex items-center gap-3 mb-3">
                      <Users className="w-5 h-5 text-primary" />
                      <span className="text-sm text-muted-foreground">Completion Rate</span>
                    </div>
                    <p className="text-3xl font-bold text-foreground">96%</p>
                    <Progress value={96} className="h-2 mt-3" />
                  </CardContent>
                </Card>
              </div>
            </div>
          </TabsContent>

          {/* Student Requests Tab */}
          <TabsContent value="requests">
            <div className="space-y-4">
              <div className="flex items-center justify-between">
                <div>
                  <h2 className="text-lg font-semibold text-foreground">Pending Requests</h2>
                  <p className="text-sm text-muted-foreground">{studentRequests.length} students are waiting for your response</p>
                </div>
              </div>

              {studentRequests.map((request, i) => (
                <motion.div key={request.id} initial={{ opacity: 0, y: 15 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: i * 0.08 }}>
                  <Card className="glass border-border/50 hover:border-primary/20 transition-all">
                    <CardContent className="p-5">
                      <div className="flex items-start justify-between gap-4">
                        <div className="flex items-start gap-4">
                          <div className="w-12 h-12 rounded-full bg-secondary/10 border border-secondary/20 flex items-center justify-center text-sm font-semibold text-secondary shrink-0">
                            {request.avatar}
                          </div>
                          <div className="space-y-1">
                            <div className="flex items-center gap-2 flex-wrap">
                              <h3 className="font-semibold text-foreground">{request.name}</h3>
                              <Badge variant="outline" className="text-xs border-border text-muted-foreground">{request.grade}</Badge>
                            </div>
                            <div className="flex items-center gap-2 text-sm">
                              <BookOpen className="w-3.5 h-3.5 text-primary" />
                              <span className="text-primary">{request.subject}</span>
                              <span className="text-muted-foreground">• {request.sessions} sessions/week</span>
                            </div>
                            <p className="text-sm text-muted-foreground mt-2">{request.message}</p>
                            <p className="text-xs text-muted-foreground mt-2">{request.date}</p>
                          </div>
                        </div>
                        <div className="flex flex-col gap-2 shrink-0">
                          <Button size="sm" className="bg-primary text-primary-foreground hover:bg-lime-glow">
                            <CheckCircle className="w-4 h-4 mr-1" /> Accept
                          </Button>
                          <Button size="sm" variant="outline" className="border-border text-muted-foreground hover:text-destructive hover:border-destructive/30">
                            <XCircle className="w-4 h-4 mr-1" /> Decline
                          </Button>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                </motion.div>
              ))}
            </div>
          </TabsContent>
        </Tabs>
      </main>
    </div>
  );
};

export default TutorDashboard;
