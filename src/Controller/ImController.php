<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ChatDialog;
use App\Entity\ChatMessage;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('im')]
class ImController extends AbstractController
{
    #[Route('/', name: 'im_index')]
    public function index(bool $isInternalMessengerEnable, EntityManagerInterface $em): Response
    {
        if (!$isInternalMessengerEnable) {
            return $this->redirectToRoute('homepage');
        }

        $dialogs = $em->getRepository(ChatDialog::class)->findBy(['owner' => $this->getUser()], ['last_message_date' => 'DESC']);

        return $this->render('im/index.html.twig', [
            'dialogs' => $dialogs,
        ]);
    }

    #[Route('/{id<\d+>}/', name: 'im_dialog')]
    public function dialog(int $id, bool $isInternalMessengerEnable, EntityManagerInterface $em, Request $request): Response
    {
        if (!$isInternalMessengerEnable) {
            return $this->redirectToRoute('homepage');
        }

        $dialog = $em->find(ChatDialog::class, $id);

        if (!$dialog or $dialog->getOwner() !== $this->getUser()) {
            return $this->redirectToRoute('im_index');
        }

        if ($request->isMethod('POST')) {
            $this->forward('App\Controller\ImController::sendMessage', $request->request->all());

            return $this->redirectToRoute('im_dialog', ['id' => $id]);
        }

        $em->getRepository(ChatMessage::class)->markAsReadForRecipient($dialog->getRecipient(), $dialog->getOwner());

        $dialog->setUnreadOwnerCount(0);

        $recipientDialog = $em->getRepository(ChatDialog::class)->findOneBy([
            'owner' => $dialog->getRecipient(),
            'recipient' => $this->getUser(),
        ]);

        if ($recipientDialog) {
            $recipientDialog->setUnreadRecipientCount(0);
        }

        $em->flush();

        $limit = 50;

        $messages = $em->getRepository(ChatMessage::class)->findForParticipants($dialog->getOwner(), $dialog->getRecipient(), $limit);

        return $this->render('im/dialog.html.twig', [
            'dialog' => $dialog,
            'messages' => array_reverse($messages),
            'limit' => $limit,
        ]);
    }

    #[Route('/{id<\d+>}/messages', name: 'im_dialog_messages')]
    public function dialogMessages(int $id, bool $isInternalMessengerEnable, EntityManagerInterface $em, Request $request): Response
    {
        if (!$isInternalMessengerEnable) {
            return $this->redirectToRoute('homepage');
        }

        $dialog = $em->find(ChatDialog::class, $id);

        if (!$dialog or $dialog->getOwner() !== $this->getUser()) {
            return $this->redirectToRoute('im_index');
        }

        $limit = (int) $request->query->get('limit', 50);
        $offset = (int) $request->query->get('offset', 0);

        $messages = $em->getRepository(ChatMessage::class)->findForParticipants($dialog->getOwner(), $dialog->getRecipient(), $limit, $offset);

        return $this->render('im/messages.html.twig', [
            'dialog' => $dialog,
            'messages' => array_reverse($messages),
        ]);
    }

    #[Route('/send_message', name: 'im_send_message', methods: ['POST'])]
    public function sendMessage(bool $isInternalMessengerEnable, EntityManagerInterface $em, Request $request): JsonResponse
    {
        if (!$isInternalMessengerEnable) {
            return $this->json([], 403);
        }

        // @todo запаковать в сервис

        $text = trim((string) $request->request->get('text'));

        if (!strlen($text)) {
            return $this->json(['message' => 'Нужно написать текст соообщения'], 400);
        }

        $recipient = $em->getRepository(User::class)->findOneBy(['id' => (string) $request->request->get('user_id')]);

        if (!$recipient) {
            return $this->json(['message' => 'Нет получателя'], 404);
        }

        $ownerDialog = $em->getRepository(ChatDialog::class)->findOneBy([
            'owner' => $this->getUser(),
            'recipient' => $recipient,
        ]);

        if (!$ownerDialog) {
            $ownerDialog = new ChatDialog($this->getUser(), $recipient);
            $em->persist($ownerDialog);
        }

        $recipientDialog = $em->getRepository(ChatDialog::class)->findOneBy([
            'owner' => $recipient,
            'recipient' => $this->getUser(),
        ]);

        if (!$recipientDialog) {
            $recipientDialog = new ChatDialog($recipient, $this->getUser());
            $em->persist($recipientDialog);
        }

        $chatMessage = new ChatMessage($this->getUser(), $recipient, $text);

        $ownerDialog
            ->setLastMessageText($text)
            ->incrementUnreadRecipientCount()
        ;

        $recipientDialog
            ->setLastMessageText($text)
            ->incrementUnreadOwnerCount()
        ;

        $em->persist($chatMessage);
        $em->flush();

        $this->addFlash('success', 'Сообщение отправлено'); // @todo remove

        return $this->json(['message' => 'ok']);
    }
}
