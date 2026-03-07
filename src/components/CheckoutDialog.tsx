import { useState } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { CheckCircle, CreditCard, Smartphone } from "lucide-react";
import { toast } from "sonner";

interface CheckoutDialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
}

const CheckoutDialog = ({ open, onOpenChange }: CheckoutDialogProps) => {
  const [paymentMethod, setPaymentMethod] = useState<"upi" | "card">("upi");
  const [upiId, setUpiId] = useState("");
  const [transactionId, setTransactionId] = useState("");
  const [submitted, setSubmitted] = useState(false);

  const handleSubmit = () => {
    if (paymentMethod === "upi" && (!upiId || !transactionId)) {
      toast.error("Please fill in all UPI details");
      return;
    }
    setSubmitted(true);
    toast.success("Booking confirmed!");
  };

  const handleClose = (val: boolean) => {
    if (!val) {
      setSubmitted(false);
      setUpiId("");
      setTransactionId("");
    }
    onOpenChange(val);
  };

  return (
    <Dialog open={open} onOpenChange={handleClose}>
      <DialogContent className="bg-card border-border sm:max-w-md">
        {submitted ? (
          <div className="py-8 text-center">
            <div className="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4">
              <CheckCircle className="w-8 h-8 text-primary" />
            </div>
            <h3 className="text-xl font-bold text-foreground mb-2">Booking Confirmed!</h3>
            <p className="text-muted-foreground text-sm mb-6">
              Your session has been booked. You'll receive a confirmation email shortly.
            </p>
            <Button onClick={() => handleClose(false)} className="bg-primary text-primary-foreground hover:bg-lime-glow">
              Done
            </Button>
          </div>
        ) : (
          <>
            <DialogHeader>
              <DialogTitle className="text-foreground text-xl">Checkout</DialogTitle>
            </DialogHeader>

            <div className="space-y-6 pt-2">
              {/* Order Summary */}
              <div className="bg-muted/30 rounded-xl p-4 space-y-2">
                <div className="flex justify-between text-sm">
                  <span className="text-muted-foreground">Tutor</span>
                  <span className="text-foreground font-medium">Dr. Ananya Sharma</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-muted-foreground">Subject</span>
                  <span className="text-foreground">Mathematics</span>
                </div>
                <div className="flex justify-between text-sm">
                  <span className="text-muted-foreground">Duration</span>
                  <span className="text-foreground">1 hour</span>
                </div>
                <div className="border-t border-border/50 pt-2 mt-2 flex justify-between font-semibold">
                  <span className="text-foreground">Total</span>
                  <span className="text-primary">₹850</span>
                </div>
              </div>

              {/* Payment Method Toggle */}
              <div>
                <Label className="text-foreground mb-3 block">Payment Method</Label>
                <div className="grid grid-cols-2 gap-3">
                  <button
                    onClick={() => setPaymentMethod("upi")}
                    className={`flex items-center gap-2 justify-center p-3 rounded-xl text-sm font-medium transition-all ${
                      paymentMethod === "upi"
                        ? "bg-primary text-primary-foreground glow-lime"
                        : "bg-muted/50 text-muted-foreground hover:text-foreground"
                    }`}
                  >
                    <Smartphone className="w-4 h-4" /> UPI
                  </button>
                  <button
                    onClick={() => setPaymentMethod("card")}
                    className={`flex items-center gap-2 justify-center p-3 rounded-xl text-sm font-medium transition-all ${
                      paymentMethod === "card"
                        ? "bg-primary text-primary-foreground glow-lime"
                        : "bg-muted/50 text-muted-foreground hover:text-foreground"
                    }`}
                  >
                    <CreditCard className="w-4 h-4" /> Card
                  </button>
                </div>
              </div>

              {paymentMethod === "upi" ? (
                <div className="space-y-4">
                  <div className="space-y-2">
                    <Label className="text-foreground">UPI ID</Label>
                    <Input
                      value={upiId}
                      onChange={(e) => setUpiId(e.target.value)}
                      placeholder="yourname@upi"
                      className="bg-muted/50 border-border text-foreground placeholder:text-muted-foreground focus-visible:ring-primary"
                    />
                  </div>
                  <div className="space-y-2">
                    <Label className="text-foreground">UPI Transaction ID</Label>
                    <Input
                      value={transactionId}
                      onChange={(e) => setTransactionId(e.target.value)}
                      placeholder="Enter 12-digit transaction ID"
                      className="bg-muted/50 border-border text-foreground placeholder:text-muted-foreground focus-visible:ring-primary"
                    />
                    <p className="text-xs text-muted-foreground">Complete the payment on your UPI app and enter the transaction ID here.</p>
                  </div>
                </div>
              ) : (
                <div className="space-y-4">
                  <div className="space-y-2">
                    <Label className="text-foreground">Card Number</Label>
                    <Input placeholder="4242 4242 4242 4242" className="bg-muted/50 border-border text-foreground placeholder:text-muted-foreground focus-visible:ring-primary" />
                  </div>
                  <div className="grid grid-cols-2 gap-3">
                    <div className="space-y-2">
                      <Label className="text-foreground">Expiry</Label>
                      <Input placeholder="MM/YY" className="bg-muted/50 border-border text-foreground placeholder:text-muted-foreground focus-visible:ring-primary" />
                    </div>
                    <div className="space-y-2">
                      <Label className="text-foreground">CVC</Label>
                      <Input placeholder="123" className="bg-muted/50 border-border text-foreground placeholder:text-muted-foreground focus-visible:ring-primary" />
                    </div>
                  </div>
                </div>
              )}

              <Button
                onClick={handleSubmit}
                className="w-full h-12 bg-primary text-primary-foreground hover:bg-lime-glow font-semibold text-base"
              >
                Pay ₹850
              </Button>
            </div>
          </>
        )}
      </DialogContent>
    </Dialog>
  );
};

export default CheckoutDialog;
