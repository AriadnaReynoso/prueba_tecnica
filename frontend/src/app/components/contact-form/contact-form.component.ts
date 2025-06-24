import { ChangeDetectionStrategy, Component, inject, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, FormArray, Validators, ReactiveFormsModule } from '@angular/forms';
import { ContactService } from '../../services/contact.service';
import { Contact } from '../../models/contact.model';
import { ActivatedRoute, Router } from '@angular/router';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-contact-form',
  templateUrl: './contact-form.component.html',
  standalone: true,
  imports: [ReactiveFormsModule, CommonModule],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ContactFormComponent implements OnInit {
  private fb = inject(FormBuilder);
  private contactService = inject(ContactService);
  private route = inject(ActivatedRoute);
  private router = inject(Router);

  contactForm: FormGroup;
  isEditMode = false;
  contactId: string | null = null;

  constructor() {
    this.contactForm = this.fb.group({
      name: ['', [Validators.required, Validators.minLength(3)]],
      email: ['', [Validators.required, Validators.email]],
      phones: this.fb.array([this.createPhoneField()], [Validators.required])
    });
    this.contactForm.statusChanges.subscribe(status => {
      if (status === 'INVALID') {
        console.log('Form errors:', {
          name: this.contactForm.get('name')?.errors,
          email: this.contactForm.get('email')?.errors,
          phones: this.phones.errors,
          phoneControls: this.phones.controls.map(c => c.get('phone')?.errors)
        });
      }
    });
  }

  ngOnInit(): void {
    this.contactId = this.route.snapshot.paramMap.get('id');
    if (this.contactId) {
      this.isEditMode = true;
      this.loadContact(this.contactId);
    }
  }

  get phones(): FormArray {
    return this.contactForm.get('phones') as FormArray;
  }

  createPhoneField(phone: string = ''): FormGroup {
    return this.fb.group({
      phone: [phone, [Validators.required, Validators.pattern(/^\d{3}-\d{3}-\d{4}$/)]]
    });
  }

  addPhone(): void {
    this.phones.push(this.createPhoneField());
  }

  removePhone(index: number): void {
    if (this.phones.length > 1) {
      this.phones.removeAt(index);
    }
  }

  loadContact(id: string): void {
    this.contactService.getContactById(id).subscribe(contact => {
      if (contact) {
        this.contactForm.patchValue({
          name: contact.name,
          email: contact.email
        });
        this.phones.clear();
        contact.phones.forEach(phone => {
          this.phones.push(this.createPhoneField(phone));
        });
      }
    });
  }

  onSubmit(): void {
    if (this.contactForm.valid) {
      const contact: Contact = {
        id: this.contactId || '',
        name: this.contactForm.value.name,
        email: this.contactForm.value.email,
        phones: this.contactForm.value.phones
          .map((p: { phone: string }) => p.phone)
          .filter((phone: string) => phone)
      };

      if (this.isEditMode) {
        this.contactService.updateContact(contact);
      } else {
        this.contactService.addContact(contact);
      }
      this.router.navigate(['/']);
    } else {
      console.log('Form invalid, errors:', this.contactForm.errors, 'Controls:', {
        name: this.contactForm.get('name')?.errors,
        email: this.contactForm.get('email')?.errors,
        phones: this.phones.errors,
        phoneControls: this.phones.controls.map(c => c.get('phone')?.errors)
      });
      this.contactForm.markAllAsTouched(); // Mostrar todos los errores en la UI
    }
  }

  onCancel(): void {
    this.router.navigate(['/']);
  }
}
