import { ChangeDetectionStrategy, Component, input, output } from '@angular/core';
import { Contact } from '../../models/contact.model';

@Component({
  selector: 'app-contact-item',
  templateUrl: './contact-item.component.html',
  standalone: true,
  changeDetection: ChangeDetectionStrategy.OnPush
})
export class ContactItemComponent {
  contact = input.required<Contact>();
  editContact = output<string>();
  deleteContact = output<string>();
}
