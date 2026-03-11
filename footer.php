<?php // partials/footer.php ?>
</main>

<!-- ═══════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════ -->
<footer style="background: var(--navy-950); border-top: 1px solid rgba(255,255,255,0.06); margin-top: 80px;">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-10">

      <!-- Brand -->
      <div class="md:col-span-1">
        <div class="flex items-center gap-2.5 mb-4">
          <div style="background: linear-gradient(135deg, var(--lime), var(--cyan)); border-radius: 8px; width:28px; height:28px; display:flex; align-items:center; justify-content:center;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#04090f" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
          </div>
          <span style="font-family:'Space Grotesk',sans-serif; font-weight:700;">Find<span class="gradient-text">Learn</span>Glow</span>
        </div>
        <p class="text-slate-500 text-sm leading-relaxed">Connecting students with verified expert tutors across all subjects.</p>
      </div>

      <!-- Links -->
      <div>
        <h4 style="font-family:'Space Grotesk',sans-serif; font-weight:600; color: var(--lime); font-size:0.85rem; letter-spacing:0.08em; text-transform:uppercase; margin-bottom:1rem;">Platform</h4>
        <ul class="space-y-2">
          <li><a href="tutors.php" class="text-slate-500 hover:text-white text-sm transition-colors">Find Tutors</a></li>
          <li><a href="register.php" class="text-slate-500 hover:text-white text-sm transition-colors">Become a Tutor</a></li>
          <li><a href="dashboard.php" class="text-slate-500 hover:text-white text-sm transition-colors">Dashboard</a></li>
        </ul>
      </div>

      <div>
        <h4 style="font-family:'Space Grotesk',sans-serif; font-weight:600; color: var(--lime); font-size:0.85rem; letter-spacing:0.08em; text-transform:uppercase; margin-bottom:1rem;">Subjects</h4>
        <ul class="space-y-2">
          <li><span class="text-slate-500 text-sm">Mathematics</span></li>
          <li><span class="text-slate-500 text-sm">Physics & Chemistry</span></li>
          <li><span class="text-slate-500 text-sm">English & Languages</span></li>
          <li><span class="text-slate-500 text-sm">Computer Science</span></li>
        </ul>
      </div>

      <div>
        <h4 style="font-family:'Space Grotesk',sans-serif; font-weight:600; color: var(--lime); font-size:0.85rem; letter-spacing:0.08em; text-transform:uppercase; margin-bottom:1rem;">Support</h4>
        <ul class="space-y-2">
          <li><a href="#" class="text-slate-500 hover:text-white text-sm transition-colors">Help Center</a></li>
          <li><a href="#" class="text-slate-500 hover:text-white text-sm transition-colors">Privacy Policy</a></li>
          <li><a href="#" class="text-slate-500 hover:text-white text-sm transition-colors">Terms of Service</a></li>
        </ul>
      </div>
    </div>

    <div style="border-top: 1px solid rgba(255,255,255,0.06);" class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
      <p class="text-slate-600 text-sm">&copy; <?= date('Y') ?> FindLearnGlow. All rights reserved.</p>
      <p class="text-slate-600 text-xs">Built for WAMP · PHP + MySQL</p>
    </div>
  </div>
</footer>

<script>
  // Initialise Lucide icons
  if (typeof lucide !== 'undefined') lucide.createIcons();
</script>
</body>
</html>