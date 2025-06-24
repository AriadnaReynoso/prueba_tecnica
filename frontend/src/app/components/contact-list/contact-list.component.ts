import { ChangeDetectionStrategy, Component, inject } from '@angular/core';
import { ContactService } from '../../services/contact.service';
import { Router } from '@angular/router';
import { ContactItemComponent } from '../contact-item/contact-item.component';

@Component({
  selector: 'app-contact-list',
  templateUrl: './contact-list.component.html',
  standalone: true,
  imports: [ContactItemComponent],
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ContactListComponent {
  private contactService = inject(ContactService);
  private router = inject(Router);
  contacts = this.contactService.contactsSignal;

  constructor() {
    console.log('ContactListComponent inicializado, contacts:', this.contacts());
  }

  addContact(): void {
    this.router.navigate(['/add']);
  }

  onEditContact(id: string): void {
    this.router.navigate(['/edit', id]);
  }

  onDeleteContact(id: string): void {
    if (confirm('¿Estás seguro de eliminar este contacto?')) {
      this.contactService.deleteContact(id);
    }
  }
}
