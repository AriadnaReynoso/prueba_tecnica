import { Injectable, signal, computed } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, of } from 'rxjs';
import { catchError, tap } from 'rxjs/operators';
import { Contact } from '../models/contact.model';

@Injectable({
  providedIn: 'root'
})
export class ContactService {
  private storageKey = 'contacts';
  private contacts = signal<Contact[]>([]);
  private apiUrl = 'contacts.json';

  // Signal para los contactos
  contactsSignal = computed(() => {
    const contacts = this.contacts();
    return contacts;
  });

  constructor(private http: HttpClient) {
    this.initializeStorage();
  }

  // Inicializa localStorage o carga desde JSON
  private initializeStorage(): void {
    const storedContacts = localStorage.getItem(this.storageKey);
    if (storedContacts) {
      try {
        const parsedContacts = JSON.parse(storedContacts);
        this.contacts.set(parsedContacts);
        console.log('cargando contactos del localStorage:', parsedContacts);
      } catch (error) {
        console.error('Error al cargar los contactos:', error);
        this.loadFromJson();
      }
    } else {
      this.loadFromJson();
    }
  }

  // Carga contactos desde el archivo JSON
  private loadFromJson(): void {
    this.http.get<Contact[]>(this.apiUrl).pipe(
      tap(contacts => {
        this.contacts.set(contacts);
        localStorage.setItem(this.storageKey, JSON.stringify(contacts));
      }),
      catchError(error => {
        console.error('Error error al cargar contactos del JSON:', error);
        this.contacts.set([]);
        localStorage.setItem(this.storageKey, JSON.stringify([]));
        return of([]);
      })
    ).subscribe();
  }

  // obtener un contacto por ID
  getContactById(id: string): Observable<Contact | undefined> {
    const contact = this.contacts().find(c => c.id === id);
    return of(contact);
  }

  // Agregar un nuevo contacto
  addContact(contact: Contact): void {
    const newContact = { ...contact, id: this.generateId() };
    this.contacts.update(contacts => [...contacts, newContact]);
    localStorage.setItem(this.storageKey, JSON.stringify(this.contacts()));
  }

  // Actualizar un contacto existente
  updateContact(contact: Contact): void {
    this.contacts.update(contacts =>
      contacts.map(c => (c.id === contact.id ? contact : c))
    );
    localStorage.setItem(this.storageKey, JSON.stringify(this.contacts()));
  }

  // eliminar un contacto
  deleteContact(id: string): void {
    this.contacts.update(contacts => contacts.filter(c => c.id !== id));
    localStorage.setItem(this.storageKey, JSON.stringify(this.contacts()));
  }

  // Generar un ID único
  private generateId(): string {
    return Math.random().toString(36).substr(2, 9);
  }
}
