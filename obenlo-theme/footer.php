<?php
/**
 * Obenlo Marketplace Master Footer
 */
?>
</div> <footer class="bg-slate-50 border-t border-slate-100 pt-20 pb-12 mt-auto">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
            <div class="col-span-1 md:col-span-2">
                <a href="<?php echo home_url(); ?>" class="text-2xl font-black tracking-tighter uppercase mb-6 block">
                    Obenlo<span class="text-indigo-600">.</span>
                </a>
                <p class="text-slate-400 font-bold uppercase tracking-tight text-[11px] max-w-xs leading-relaxed">
                    The professional gateway for high-performance marketplace transactions. Securely connecting Hosts and Buyers globally.
                </p>
            </div>
            <div>
                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-900 mb-6">Platform</h4>
                <ul class="space-y-4 text-[11px] font-bold uppercase tracking-widest text-slate-400">
                    <li><a href="<?php echo home_url('/listings'); ?>" class="hover:text-indigo-600 transition-colors">Marketplace</a></li>
                    <li><a href="<?php echo home_url('/host-console'); ?>" class="hover:text-indigo-600 transition-colors">Host Center</a></li>
                    <li><a href="<?php echo home_url('/login-hub'); ?>" class="hover:text-indigo-600 transition-colors">Authentication</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-900 mb-6">Security</h4>
                <ul class="space-y-4 text-[11px] font-bold uppercase tracking-widest text-slate-400">
                    <li><a href="#" class="hover:text-indigo-600 transition-colors">Privacy Policy</a></li>
                    <li><a href="#" class="hover:text-indigo-600 transition-colors">Terms of Service</a></li>
                    <li><a href="#" class="hover:text-indigo-600 transition-colors">Stripe Protection</a></li>
                </ul>
            </div>
        </div>
        
        <div class="pt-8 border-t border-slate-200/50 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-[9px] font-black uppercase text-slate-300 tracking-[0.3em]">
                &copy; <?php echo date('Y'); ?> Obenlo.com. All Rights Reserved.
            </p>
            <div class="flex gap-4">
                <div class="h-4 w-8 bg-slate-200 rounded-sm opacity-50"></div>
                <div class="h-4 w-8 bg-slate-200 rounded-sm opacity-50"></div>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
