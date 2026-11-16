<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. ERP Customers / Accounts (Companies & Individuals)
        Schema::create('crm_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('customer_code')->nullable()->index(); // e.g. CUST-1001
            $table->string('type')->default('company'); // company, individual
            $table->string('name'); // Company Name or Individual Name
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable()->index();
            $table->string('website')->nullable();
            $table->string('tax_id')->nullable(); // VAT / Trade License
            $table->text('billing_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Bangladesh');
            $table->string('payment_terms')->default('Net 30'); // Cash, Net 15, Net 30, COD
            $table->decimal('credit_limit', 14, 2)->default(0.00);
            $table->decimal('total_revenue', 14, 2)->default(0.00);
            $table->string('status')->default('active'); // active, inactive, blacklisted
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('custom_fields')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. ERP Leads (Inbound Qualification & Conversion)
        Schema::create('crm_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('lead_code')->nullable()->index(); // e.g. LEAD-2026-001
            $table->string('title'); // e.g. "50 Units Gaming Laptop Inquiry"
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('source')->default('social_media'); // facebook, whatsapp, telegram, webchat, website_form, direct_call, referral
            $table->foreignId('channel_id')->nullable()->constrained('channels')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete(); // Linked Social Contact
            $table->string('status')->default('new'); // new, contacted, qualified, converted, lost
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->decimal('estimated_budget', 14, 2)->default(0.00);
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('converted_customer_id')->nullable()->constrained('crm_customers')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->text('lost_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. ERP Deals / Opportunities (Sales Pipeline with Quotation Items)
        Schema::create('crm_deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('deal_code')->nullable()->index(); // e.g. DEAL-5001
            $table->string('title'); // e.g. "Apex Supply Contract - Q4"
            $table->foreignId('customer_id')->nullable()->constrained('crm_customers')->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('crm_leads')->nullOnDelete();
            $table->string('stage')->default('qualification'); // qualification, proposal, negotiation, won, lost
            $table->decimal('amount', 14, 2)->default(0.00);
            $table->unsignedTinyInteger('probability')->default(20); // 10% to 100%
            $table->date('expected_close_date')->nullable();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('lost_reason')->nullable(); // price, competitor, out_of_stock, cancelled
            $table->json('quotation_items')->nullable(); // Product rows: [{item, qty, rate, tax, subtotal}]
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. ERP Activities & Tasks (Calls, Meetings, Follow-up Deadlines)
        Schema::create('crm_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('crm_customers')->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('crm_leads')->cascadeOnDelete();
            $table->foreignId('deal_id')->nullable()->constrained('crm_deals')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Assigned sales rep
            $table->string('type')->default('call'); // call, meeting, follow_up, task, quotation_sent
            $table->string('subject'); // e.g. "Introductory product demo call"
            $table->text('description')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->string('status')->default('pending'); // pending, completed, cancelled, overdue
            $table->dateTime('completed_at')->nullable();
            $table->text('outcome_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_activities');
        Schema::dropIfExists('crm_deals');
        Schema::dropIfExists('crm_leads');
        Schema::dropIfExists('crm_customers');
    }
};
